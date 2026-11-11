<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Poids;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;

// Le formulaire recharge les zones à chaque changement de transporteur (route transport.zone) et remet la zone à zéro.
// Le serveur est le dernier garde-fou : une zone qui ne fait pas partie du transporteur ne doit jamais être
// enregistrée (tarif inatteignable par ShippingService::resolve, qui filtre sur transport + zone).

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]));

    $this->poids = Poids::create(['min' => 0, 'max' => 5]);
    $this->zoneA = Zone::create(['nom' => 'Afrique']);
    $this->zoneB = Zone::create(['nom' => 'Europe']);
    $this->dhl = Transport::create(['nom' => 'DHL']);
    $this->dhl->zones()->attach($this->zoneA->id);
    $this->ups = Transport::create(['nom' => 'UPS']);
    $this->ups->zones()->attach($this->zoneB->id);
});

/** @return array<string, mixed> */
function shippingPayload(array $override = []): array
{
    return array_merge([
        'transport_id' => test()->dhl->id,
        'zone_id' => (string) test()->zoneA->id,
        'poids_id' => (string) test()->poids->id,
        'temps' => '3 jours',
        'montant' => 12000,
    ], $override);
}

it('lists the zones of a transport for the dependent select', function () {
    $this->getJson("/admin/transport/{$this->dhl->id}")
        ->assertOk()
        ->assertExactJson([['label' => 'Afrique', 'value' => (string) $this->zoneA->id]]);
});

it('stores a shipping whose zone belongs to the transport', function () {
    $this->post('/admin/shipping', shippingPayload())->assertSessionHasNoErrors()->assertRedirect();

    expect(Shipping::count())->toBe(1);
});

it('rejects a zone that does not belong to the chosen transport on store', function () {
    $this->post('/admin/shipping', shippingPayload(['zone_id' => (string) $this->zoneB->id]))
        ->assertSessionHasErrors('zone_id');

    expect(Shipping::count())->toBe(0);
});

it('rejects a stale zone when the transport is changed on update', function () {
    $shipping = Shipping::create(shippingPayload());

    // Le transporteur passe à UPS mais la zone d'origine (Afrique) est restée sélectionnée.
    $this->patch("/admin/shipping/{$shipping->id}", shippingPayload(['transport_id' => $this->ups->id]))
        ->assertSessionHasErrors('zone_id');

    expect($shipping->refresh()->transport_id)->toBe($this->dhl->id);
});

it('accepts the new transport with one of its own zones on update', function () {
    $shipping = Shipping::create(shippingPayload());

    $this->patch("/admin/shipping/{$shipping->id}", shippingPayload([
        'transport_id' => $this->ups->id,
        'zone_id' => (string) $this->zoneB->id,
    ]))->assertSessionHasNoErrors();

    expect($shipping->refresh()->transport_id)->toBe($this->ups->id);
});
