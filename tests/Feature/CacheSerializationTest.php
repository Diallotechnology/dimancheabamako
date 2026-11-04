<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Service\CartService;
use App\Service\CategoryService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Laravel 13 : l'allow-list `serializable_classes` ne s'applique qu'aux stores qui sérialisent.
    config(['cache.stores.array.serialize' => true, 'cache.default' => 'array']);
    Cache::purge('array');
    Cache::flush();
});

it('restricts cache unserialization to an explicit allow-list', function () {
    expect(config('cache.serializable_classes'))->toBeArray()->not->toBeEmpty();
});

it('still reads cached categories as models when unserialization is restricted', function () {
    Category::factory()->create(['nom' => 'Bazin']);
    $service = new CategoryService();

    $service->all();               // remplit le cache
    $cached = $service->all();     // relit depuis le cache sérialisé

    expect($cached->first())->toBeInstanceOf(Category::class)
        ->and($cached->first()->nom)->toBe('Bazin');
});

it('still reads the cart from the cache when unserialization is restricted', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['categorie_id' => $category->id, 'prix' => 20000, 'stock' => 10, 'is_preorder' => false]);

    app(CartService::class)->add($product->id, $product->nom, 20000, 10, 1.0);

    $fresh = app()->make(CartService::class);
    $line = $fresh->get($product->id);

    expect($line['quantity'])->toBe(1)
        ->and((int) $fresh->getTotal())->toBe(20000)
        ->and($line['added_at'])->toBeInstanceOf(Carbon::class);
});

it('renders the cart page from a serialized cache', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['categorie_id' => $category->id, 'prix' => 20000, 'stock' => 10, 'is_preorder' => false]);
    app(CartService::class)->add($product->id, $product->nom, 20000, 10, 1.0);

    $this->get('/panier')->assertOk();
});
