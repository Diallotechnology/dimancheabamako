<?php

declare(strict_types=1);

use App\Enum\OrderEnum;
use App\Enum\RoleEnum;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;

function makeAdminOrder(array $attributes = []): Order
{
    $zone = Zone::firstOrCreate(['nom' => 'Afrique']);
    $country = Country::firstOrCreate(['nom' => 'Mali'], ['zone_id' => $zone->id]);
    $transport = Transport::firstOrCreate(['nom' => 'DHL']);
    $client = Client::firstOrCreate(['email' => 'awa@example.test'], [
        'prenom' => 'Awa', 'nom' => 'Traoré', 'contact' => '70000000', 'pays' => 'Mali',
    ]);

    return Order::create($attributes + [
        'client_id' => $client->id,
        'country_id' => $country->id,
        'transport_id' => $transport->id,
        'reference' => uniqid('ref'),
        'adresse' => 'Rue 12',
        'ville' => 'Bamako',
        'poids' => '1',
        'shipping' => '15000',
        'etat' => OrderEnum::SAVE->value,
    ]);
}

it('filters the order list by status and exposes the client contact', function () {
    makeAdminOrder(['etat' => OrderEnum::TERMINE->value, 'reference' => 'DONE-1']);
    makeAdminOrder(['etat' => OrderEnum::EN_COURS->value, 'reference' => 'LIVE-1']);

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->get('/admin/order?status='.urlencode(OrderEnum::TERMINE->value))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Order/Index')
            ->has('rows.data', 1)
            ->where('rows.data.0.reference', 'DONE-1')
            ->where('rows.data.0.client.email', 'awa@example.test'));
});

it('deletes an order and answers with the JSON success payload read by ConfirmDelete', function () {
    $order = makeAdminOrder();

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->deleteJson("/admin/order/{$order->id}")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Order::find($order->id))->toBeNull();
});

it('updates the state of an order and rejects an unknown state', function () {
    $order = makeAdminOrder();
    $user = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);

    $this->actingAs($user)
        ->patch("/admin/order/{$order->id}", ['etat' => OrderEnum::EN_COURS->value])
        ->assertRedirect();
    expect($order->refresh()->etat)->toBe(OrderEnum::EN_COURS->value);

    $this->actingAs($user)
        ->patch("/admin/order/{$order->id}", ['etat' => 'Inconnu'])
        ->assertSessionHasErrors('etat');
    expect($order->refresh()->etat)->toBe(OrderEnum::EN_COURS->value);
});

it('filters the order list with the search text', function () {
    makeAdminOrder(['reference' => 'ALPHA-1']);
    makeAdminOrder(['reference' => 'BRAVO-1']);

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->get('/admin/order?search=ALPHA')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('rows.data', 1)
            ->where('rows.data.0.reference', 'ALPHA-1'));
});
