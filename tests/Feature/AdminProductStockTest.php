<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

// Stock admin : l'édition applique un delta (stock saisi − stock affiché à l'ouverture du formulaire)
// sur le stock réel verrouillé, pour ne pas écraser les réservations faites entre-temps.

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
    $this->product = Product::factory()->create([
        'categorie_id' => Category::factory()->create()->id,
        'stock' => 10,
    ]);
});

function stockUpdatePayload(Product $product, array $extra = []): array
{
    return array_merge([
        'categorie_id' => $product->categorie_id,
        'reference' => $product->reference,
        'nom' => $product->nom,
        'color' => 'rouge',
        'taille' => 'M',
        'resume' => 'résumé',
        'poids' => '1',
        'favoris' => 0,
        'is_preorder' => 0,
        'prix' => 5000,
    ], $extra);
}

it('keeps stock reserved by an order placed while the admin form was open', function () {
    // Le formulaire a été ouvert avec stock = 10, puis une commande réserve 3 unités.
    $this->product->decrement('stock', 3);

    $this->actingAs($this->admin)
        ->put("/admin/product/{$this->product->id}", stockUpdatePayload($this->product, ['stock' => 10, 'stock_initial' => 10, 'prix' => 6000]))
        ->assertSessionHasNoErrors();

    expect($this->product->fresh()->stock)->toBe(7)
        ->and($this->product->fresh()->prix)->toBe(6000);
});

it('applies the admin stock correction as a delta on the real stock', function () {
    $this->product->decrement('stock', 3);

    $this->actingAs($this->admin)
        ->put("/admin/product/{$this->product->id}", stockUpdatePayload($this->product, ['stock' => 15, 'stock_initial' => 10]))
        ->assertSessionHasNoErrors();

    expect($this->product->fresh()->stock)->toBe(12);
});

it('rejects a correction that would make the real stock negative', function () {
    $this->product->decrement('stock', 8);

    $this->actingAs($this->admin)
        ->put("/admin/product/{$this->product->id}", stockUpdatePayload($this->product, ['stock' => 5, 'stock_initial' => 10]))
        ->assertSessionHasErrors('stock');

    expect($this->product->fresh()->stock)->toBe(2);
});

it('requires the initial stock shown in the form', function () {
    $this->actingAs($this->admin)
        ->put("/admin/product/{$this->product->id}", stockUpdatePayload($this->product, ['stock' => 12]))
        ->assertSessionHasErrors('stock_initial');
});
