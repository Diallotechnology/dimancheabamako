<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Transport;
use App\Models\Zone;

function pendingOrderFixture(array $overrides = []): Order
{
    return Order::factory()->create(array_merge([
        'client_id' => Client::factory()->create()->id,
        'country_id' => Country::factory()->create(['zone_id' => Zone::factory()->create()->id])->id,
        'transport_id' => Transport::factory()->create()->id,
    ], $overrides));
}

it('succeeds when no order is pending without a reference', function () {
    pendingOrderFixture(['reference' => 'DAB2026-1', 'trans_ref' => 'paid-ref']);
    pendingOrderFixture(['reference' => null, 'trans_ref' => null]);

    $this->artisan('app:check-stock-reservation-migration')
        ->expectsOutputToContain('OK')
        ->assertExitCode(0);
});

it('fails and lists orders having a trans_ref but no reference', function () {
    $pending = pendingOrderFixture(['reference' => null, 'trans_ref' => 'pending-ref-42']);
    pendingOrderFixture(['reference' => 'DAB2026-2', 'trans_ref' => 'paid-ref']);

    $this->artisan('app:check-stock-reservation-migration')
        ->expectsOutputToContain('pending-ref-42')
        ->expectsOutputToContain('ÉCHEC : 1 commande(s)')
        ->assertExitCode(1);

    expect($pending->exists)->toBeTrue();
});
