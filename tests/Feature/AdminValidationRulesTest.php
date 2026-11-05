<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;

// M3 — valeurs hors domaine refusées dans les formulaires admin (rôle, état, montants, pourcentages)

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => RoleEnum::ADMIN]);
});

it('rejects an unknown role when creating or updating a user', function () {
    $this->actingAs($this->admin)
        ->post('/admin/user', ['name' => 'X', 'email' => 'x@example.com', 'role' => 'Hacker'])
        ->assertSessionHasErrors('role');

    $target = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);

    $this->actingAs($this->admin)
        ->put("/admin/user/{$target->id}", ['name' => 'X', 'email' => $target->email, 'role' => 'Hacker'])
        ->assertSessionHasErrors('role');
});

it('rejects an unknown order state', function () {
    $order = Order::factory()->create([
        'client_id' => Client::factory()->create()->id,
        'country_id' => Country::factory()->create(['zone_id' => Zone::factory()->create()->id])->id,
        'transport_id' => Transport::factory()->create()->id,
    ]);

    $this->actingAs($this->admin)
        ->put("/admin/order/{$order->id}", ['etat' => 'Inventé'])
        ->assertSessionHasErrors('etat');
});

it('rejects negative price and stock on product creation', function () {
    $this->actingAs($this->admin)
        ->post('/admin/product', ['prix' => -5, 'stock' => -1])
        ->assertSessionHasErrors(['prix', 'stock']);
});

it('rejects a pay link amount below 1', function (int $montant) {
    $this->actingAs($this->admin)
        ->post('/admin/paylink', ['name' => 'Awa', 'contact' => '+22370123456', 'montant' => $montant])
        ->assertSessionHasErrors('montant');
})->with([0, -5000]);

it('rejects a negative shipping amount', function () {
    $this->actingAs($this->admin)
        ->post('/admin/shipping', ['montant' => -100])
        ->assertSessionHasErrors('montant');
});

it('rejects a promotion reduction outside 1-100 percent', function (int $reduction) {
    $this->actingAs($this->admin)
        ->post('/admin/promotion', ['nom' => 'Promo', 'reduction' => $reduction, 'debut' => '2026-01-01', 'fin' => '2026-02-01', 'product_id' => []])
        ->assertSessionHasErrors('reduction');
})->with([0, 101, 150, -10]);

it('rejects a weak password at checkout', function () {
    $this->post('/order', ['password' => '123'])->assertSessionHasErrors('password');
});
