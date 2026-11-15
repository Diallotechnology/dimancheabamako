<?php

declare(strict_types=1);

use App\Livewire\Counter;
use App\Livewire\QuickAddModal;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Service\CartService;
use Livewire\Livewire;

beforeEach(function () {
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);
});

function modalProduct(int $prix = 20000, int $reduction = 0, bool $preorder = false): Product
{
    $product = Product::factory()->create([
        'categorie_id' => Category::factory()->create()->id,
        'prix' => $prix,
        'stock' => 10,
        'is_preorder' => $preorder,
    ]);

    if ($reduction > 0) {
        $promo = Promotion::factory()->create([
            'reduction' => $reduction,
            'debut' => now()->subDay(),
            'fin' => now()->addDay(),
            'etat' => 'En cours',
        ]);
        $product->promotions()->attach($promo->id);
    }

    return $product;
}

it('adds the product to the cart when the add-to-cart event is received', function () {
    $product = modalProduct();

    Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: $product->id)
        ->assertDispatched('productCount')
        ->assertSee($product->nom);

    expect(app(CartService::class)->has($product->id))->toBeTrue();
});

it('does not discount the already discounted cart price a second time', function () {
    // 20 000 CFA - 10 % = 18 000 CFA : le panier stocke déjà 18 000, la modale ne doit pas retomber à 16 200.
    $product = modalProduct(20000, 10);

    Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: $product->id)
        ->assertSee('18 000 CFA')
        ->assertSee('20 000 CFA')
        ->assertDontSee('16 200');
});

it('shows the cart line total as unit price times quantity', function () {
    $product = modalProduct(20000, 10, preorder: true);

    $component = Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: $product->id)
        ->call('setQuickQuantity', 5);

    expect($component->get('addedItem')['quantity'])->toBe(5);
    $component->assertSee('90 000 CFA'); // 5 × 18 000
});

it('refuses to go to the cart before a preorder quantity is chosen', function () {
    $product = modalProduct(20000, 0, preorder: true);

    Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: $product->id)
        ->call('goToCart')
        ->assertNoRedirect();
});

it('adds nothing to the cart for an unknown product', function () {
    Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: 999999)
        ->assertNotDispatched('productCount');

    expect(app(CartService::class)->getCount())->toBe(0);
});

it('keeps the counter component in sync after an add', function () {
    $product = modalProduct();

    Livewire::test(QuickAddModal::class)
        ->dispatch('add-to-cart', id: $product->id)
        ->assertDispatched('productCount', fn ($event, $params) => true);

    Livewire::test(Counter::class)->assertSee('1');
});
