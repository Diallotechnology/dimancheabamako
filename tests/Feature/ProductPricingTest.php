<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Service\PriceService;

function makeProduct(int $prix = 20000): Product
{
    $category = Category::factory()->create();

    return Product::factory()->create(['categorie_id' => $category->id, 'prix' => $prix]);
}

function attachPromo(Product $product, int $reduction, string $debut = '-1 day', string $fin = '+1 day', string $etat = 'En cours'): void
{
    $promo = Promotion::factory()->create([
        'reduction' => $reduction,
        'debut' => now()->modify($debut),
        'fin' => now()->modify($fin),
        'etat' => $etat,
    ]);
    $product->promotions()->attach($promo->id);
}

it('returns the base price when no promotion applies', function () {
    $product = makeProduct(20000);

    expect($product->fresh()->prix_final_base)->toBe(20000)
        ->and($product->fresh()->reduction)->toBe(0);
});

it('applies the active promotion to the final price', function () {
    $product = makeProduct(20000);
    attachPromo($product, 10);

    $fresh = $product->fresh();

    expect($fresh->reduction)->toBe(10)
        ->and((float) $fresh->prix_final_base)->toBe(18000.0);
});

it('ignores expired, future and closed promotions', function (string $debut, string $fin, string $etat) {
    $product = makeProduct(20000);
    attachPromo($product, 50, $debut, $fin, $etat);

    expect($product->fresh()->reduction)->toBe(0);
})->with([
    'expired' => ['-10 days', '-1 day', 'En cours'],
    'future' => ['+1 day', '+10 days', 'En cours'],
    'closed' => ['-1 day', '+1 day', 'Terminé'],
]);

it('formats promo prices in CFA and EUR through PriceService', function () {
    $product = makeProduct(20000);
    attachPromo($product, 10);

    $service = new PriceService;

    session(['devise' => 'CFA']);
    expect($service->formatPromo($product->fresh()))->toBe('18 000 CFA');

    session(['devise' => 'EUR', 'taux_eur' => 655.957]);
    expect($service->formatPromo($product->fresh()))->toBe('27,44 €');
});

it('falls back to CFA when EUR is selected but no valid EUR rate exists', function (mixed $taux) {
    $product = makeProduct(20000);
    session(['devise' => 'EUR', 'taux_eur' => $taux]);

    expect((new PriceService)->formatBase($product))->toBe('20 000 CFA')
        ->and($product->prix_format)->toBe('20 000 CFA');
})->with([null, 0, '0', 'abc']);

it('renders the storefront without crashing when the EUR rate is missing', function () {
    $this->withSession(['devise' => 'EUR', 'taux_eur' => null])->get('/')->assertOk();
});
