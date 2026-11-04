<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Livewire\AddToCard;
use App\Livewire\Counter;
use App\Livewire\Delete;
use App\Livewire\Panier;
use App\Livewire\Produit;
use App\Models\Category;
use App\Models\Country;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use App\Service\CartService;
use Livewire\Livewire;

beforeEach(function () {
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);
    $this->category = Category::factory()->create(['nom' => 'Bazin']);
});

function shopProduct(array $overrides = []): Product
{
    return Product::factory()->create(array_merge([
        'categorie_id' => test()->category->id,
        'prix' => 20000,
        'stock' => 10,
        'is_preorder' => false,
    ], $overrides));
}

function cartWith(Product $product, int $quantity = 1): CartService
{
    $cart = app(CartService::class);
    $cart->add($product->id, $product->nom, (int) $product->prix, $product->stock, (float) $product->poids, (bool) $product->is_preorder);
    if ($quantity > 1) {
        $cart->update($product->id, $quantity);
    }

    return $cart;
}

it('serves the shop, cart and home pages', function () {
    $product = shopProduct();

    $this->get("/shop/{$this->category->id}-bazin")->assertOk();
    $this->get('/panier')->assertOk();
    $this->get('/')->assertOk();
    $this->get("/shop/show/{$product->id}-{$product->slug}")->assertOk();
});

it('lists in-stock products of the category and hides out-of-stock ones', function () {
    $visible = shopProduct(['nom' => 'Visible bazin']);
    $hidden = shopProduct(['nom' => 'Rupture bazin', 'stock' => 0]);
    $other = shopProduct(['nom' => 'Autre categorie', 'categorie_id' => Category::factory()->create()->id]);

    Livewire::test(Produit::class, ['category' => $this->category])
        ->assertSee($visible->nom)
        ->assertDontSee($hidden->nom)
        ->assertDontSee($other->nom);
});

it('filters the product list with the live search', function () {
    shopProduct(['nom' => 'Alpha bleu', 'color' => 'Bleu']);
    shopProduct(['nom' => 'Omega rouge', 'color' => 'Rouge']);

    Livewire::test(Produit::class, ['category' => $this->category])
        ->set('search', 'Omega')
        ->assertSee('Omega rouge')
        ->assertDontSee('Alpha bleu');
});

it('paginates the product list by 15', function () {
    Product::factory()->count(17)->create(['categorie_id' => $this->category->id, 'stock' => 5]);

    $component = Livewire::test(Produit::class, ['category' => $this->category]);

    expect($component->viewData('rows')->count())->toBe(15)
        ->and($component->viewData('rows')->total())->toBe(17);
});

it('adds a product to the cart and opens the quick modal', function () {
    $product = shopProduct();

    Livewire::test(AddToCard::class, ['id' => $product->id])
        ->call('add')
        ->assertDispatched('openQuickModal');

    expect(app(CartService::class)->has($product->id))->toBeTrue();
});

it('does not duplicate a product already in the cart', function () {
    $product = shopProduct();
    $cart = cartWith($product, 3);

    Livewire::test(AddToCard::class, ['id' => $product->id])->call('add');

    expect($cart->get($product->id)['quantity'])->toBe(3);
});

it('removes a cart line and notifies the counter and the cart', function () {
    $product = shopProduct();
    $cart = cartWith($product);

    Livewire::test(Delete::class, ['row' => $cart->get($product->id)])
        ->call('deleteProduct')
        ->assertDispatched('productDelete')
        ->assertDispatched('productCount');

    expect($cart->has($product->id))->toBeFalse();
});

it('shows the cart item count in the counter', function () {
    $cart = cartWith(shopProduct());
    $cart->add(999, 'Autre', 1000, 5, 1.0);

    Livewire::test(Counter::class)->assertViewHas('count', 2);
});

it('recomputes the cart total when a line is deleted', function () {
    $a = shopProduct(['prix' => 20000]);
    $b = shopProduct(['prix' => 10000]);
    $cart = cartWith($a, 2);
    $cart->add($b->id, $b->nom, 10000, 10, 1.0);

    $panier = Livewire::test(Panier::class)->assertSet('Total', '50 000');

    $cart->remove($b->id);
    $panier->dispatch('productDelete')->assertSet('Total', '40 000');
});

it('requires email and password to log in from the cart', function () {
    Livewire::test(Panier::class)
        ->call('login_submit')
        ->assertHasErrors(['email' => 'required', 'password' => 'required']);
});

it('logs in from the cart and merges the guest cart into the user cart', function () {
    $user = User::factory()->create(['role' => RoleEnum::CUSTOMER, 'password' => 'secret-pass-1']);
    $product = shopProduct();
    cartWith($product, 2);

    Livewire::test(Panier::class)
        ->set('email', $user->email)
        ->set('password', 'secret-pass-1')
        ->call('login_submit')
        ->assertRedirect(route('panier'));

    $this->assertAuthenticatedAs($user);
    expect(app(CartService::class)->setUser($user->id)->get($product->id)['quantity'])->toBe(2);
});

it('rejects a wrong password from the cart login', function () {
    $user = User::factory()->create(['role' => RoleEnum::CUSTOMER]);

    Livewire::test(Panier::class)
        ->set('email', $user->email)
        ->set('password', 'mauvais')
        ->call('login_submit')
        ->assertNoRedirect();

    $this->assertGuest();
});

function shippingFor(float $weightMax = 5, int $montant = 5000): array
{
    $zone = Zone::factory()->create();
    $country = Country::factory()->create(['zone_id' => $zone->id]);
    $transport = Transport::factory()->create();
    $poids = Poids::factory()->create(['min' => 0, 'max' => $weightMax]);
    Shipping::factory()->create([
        'zone_id' => $zone->id,
        'transport_id' => $transport->id,
        'poids_id' => $poids->id,
        'montant' => $montant,
    ]);

    return [$country, $transport];
}

it('adds the shipping cost to the displayed cart total in CFA', function () {
    [$country, $transport] = shippingFor(montant: 5000);
    cartWith(shopProduct(['prix' => 20000, 'poids' => 1]));

    Livewire::test(Panier::class)
        ->set('country_id', $country->id)
        ->set('transport_id', $transport->id)
        ->call('calculateShipping')
        ->assertSee('25 000 CFA');
});

it('adds the shipping cost to the displayed cart total in EUR', function () {
    session(['devise' => 'EUR']);
    [$country, $transport] = shippingFor(montant: 5000);
    cartWith(shopProduct(['prix' => 20000, 'poids' => 1]));

    // (20 000 + 5 000) CFA / 655,957 = 38,11 €
    Livewire::test(Panier::class)
        ->set('country_id', $country->id)
        ->set('transport_id', $transport->id)
        ->call('calculateShipping')
        ->assertSee('38,11 €');
});
