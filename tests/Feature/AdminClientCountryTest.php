<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;

beforeEach(function () {
    $zone = Zone::create(['nom' => 'Afrique']);
    Country::create(['nom' => 'Mali', 'zone_id' => $zone->id]);
    Country::create(['nom' => 'Sénégal', 'zone_id' => $zone->id]);

    $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));
});

it('stores the country name, not its id, so the options use the name as value', function () {
    $this->get('/admin/client')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Client/Index')
            ->where('country.0', ['label' => 'Mali', 'value' => 'Mali'])
            ->where('country.1', ['label' => 'Sénégal', 'value' => 'Sénégal']));
});

it('offers the same country options when editing a client', function () {
    $client = Client::create(['prenom' => 'Awa', 'nom' => 'Traoré', 'email' => 'awa@example.com', 'contact' => '70000000', 'pays' => 'Mali']);

    $this->get("/admin/client/{$client->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Client/Update')
            ->where('country.0.value', 'Mali')
            ->has('country', 2));
});

it('loads the country of each order on the client page', function () {
    $client = Client::create(['prenom' => 'Awa', 'nom' => 'Traoré', 'email' => 'awa@example.com', 'contact' => '70000000', 'pays' => 'Mali']);
    $order = Order::factory()->create(['transport_id' => Transport::create(['nom' => 'DHL'])->id, 'client_id' => $client->id, 'country_id' => Country::where('nom', 'Mali')->value('id')]);

    $this->get("/admin/client/{$client->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Client/Show')
            ->where('client.orders.0.id', $order->id)
            ->where('client.orders.0.country.nom', 'Mali'));
});
