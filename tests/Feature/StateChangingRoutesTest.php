<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// M1 — les actions qui modifient l'état ne sont plus joignables en GET (donc plus déclenchables par un simple lien)

it('does not expose state-changing admin actions over GET', function (string $name) {
    $route = Route::getRoutes()->getByName($name);

    expect($route)->not->toBeNull()
        ->and($route->methods())->not->toContain('GET');
})->with(['product.favori', 'paylink.regenerate', 'maintenance']);

it('toggles a product favorite with PUT', function () {
    $product = Product::factory()->create(['categorie_id' => Category::factory()->create()->id, 'favoris' => 0]);
    $secretary = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);

    $this->actingAs($secretary)->put("/admin/product/favoris/1/{$product->id}")->assertSuccessful();

    expect((bool) $product->fresh()->favoris)->toBeTrue();
});

it('rejects an invalid favorite value and an unknown product', function () {
    $product = Product::factory()->create(['categorie_id' => Category::factory()->create()->id]);
    $secretary = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);

    $this->actingAs($secretary)->put("/admin/product/favoris/5/{$product->id}")->assertNotFound();
    $this->actingAs($secretary)->put('/admin/product/favoris/1/99999')->assertNotFound();
});
