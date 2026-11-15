<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Slide;

beforeEach(function () {
    $this->withoutVite();
    session(['devise' => 'CFA', 'taux_eur' => 655.957]);
    $this->category = Category::factory()->create(['nom' => 'Bazin Riche']);
});

function homeProduct(array $overrides = []): Product
{
    return Product::factory()->create(array_merge([
        'categorie_id' => test()->category->id,
        'prix' => 20000,
        'stock' => 10,
        'is_preorder' => false,
    ], $overrides));
}

it('renders the home page on the shop layout, without the Evara theme', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('id="contenu"', false)
        ->assertDontSee('bootstrap')
        ->assertDontSee('jquery', false);
});

it('lists the latest and the popular products through the shared product card', function () {
    $latest = homeProduct(['nom' => 'Bazin Nouveau', 'favoris' => 0]);
    $popular = homeProduct(['nom' => 'Getzner Favori', 'favoris' => 1]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Bazin Nouveau')
        ->assertSee('Getzner Favori')
        ->assertSee("Livewire.dispatch('add-to-cart', { id: {$latest->id} })", false)
        ->assertSee("Livewire.dispatch('add-to-cart', { id: {$popular->id} })", false);
});

it('shows the promotion price, the crossed-out price and the discount badge', function () {
    $product = homeProduct(['nom' => 'Bazin Promo']);
    $promo = Promotion::factory()->create([
        'reduction' => 10,
        'debut' => now()->subDay(),
        'fin' => now()->addDay(),
        'etat' => 'En cours',
    ]);
    $product->promotions()->attach($promo->id);

    $this->get('/')
        ->assertOk()
        ->assertSee('18 000 CFA')
        ->assertSee('20 000 CFA')
        ->assertSee('Bon plan -10 %');
});

it('shows prices in EUR when the session currency is EUR', function () {
    homeProduct(['nom' => 'Bazin Euro', 'prix' => 20000]);

    $this->withSession(['devise' => 'EUR', 'taux_eur' => 655.957])->get('/')
        ->assertOk()
        ->assertSee('30,49 €');
});

it('hides out-of-stock products from the home page', function () {
    homeProduct(['nom' => 'Visible Bazin']);
    homeProduct(['nom' => 'Rupture Bazin', 'stock' => 0]);

    $this->get('/')->assertSee('Visible Bazin')->assertDontSee('Rupture Bazin');
});

it('links "view all" to the public categories page, not to the admin one', function () {
    $this->get('/')
        ->assertSee('href="'.route('categorie_mobile').'"', false)
        ->assertDontSee('href="'.url('admin/category').'"', false);
});

it('renders the slides from the back-office when there are some', function () {
    Slide::query()->create([
        'text_one' => 'Nouvelle collection',
        'text_two' => 'Bazin riche',
        'paragraph' => 'Des tissus d\'exception.',
        'image' => 'slide/a.webp',
    ]);

    $this->get('/')->assertOk()->assertSee('Nouvelle collection')->assertSee('Bazin riche');
});
