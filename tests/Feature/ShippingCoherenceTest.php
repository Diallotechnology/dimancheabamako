<?php

declare(strict_types=1);

use App\Models\Shipping;
use App\Models\Transport;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

function incoherentShippings()
{
    return Shipping::query()->whereNotExists(function ($query): void {
        $query->select(DB::raw(1))
            ->from('transport_zone')
            ->whereColumn('transport_zone.transport_id', 'shippings.transport_id')
            ->whereColumn('transport_zone.zone_id', 'shippings.zone_id');
    });
}

it('creates shippings whose zone is served by their transport', function () {
    Shipping::factory()->count(15)->create();

    expect(Shipping::count())->toBe(15)
        ->and(incoherentShippings()->count())->toBe(0);
});

it('picks the zone among those already served by the given transport', function () {
    $transport = Transport::factory()->create();
    $zones = Zone::factory()->count(2)->create();
    $transport->zones()->attach($zones->pluck('id'));

    $shippings = Shipping::factory()->count(10)->create(['transport_id' => $transport->id]);

    expect($shippings->pluck('zone_id')->diff($zones->pluck('id')))->toBeEmpty()
        ->and(Zone::count())->toBe(2);
});

it('lists incoherent shippings and fails', function () {
    $coherent = Shipping::factory()->create();
    $bad = Shipping::factory()->create([
        'transport_id' => Transport::factory()->create()->id,
        'zone_id' => Zone::factory()->create()->id,
    ]);
    DB::table('transport_zone')->where('transport_id', $bad->transport_id)->delete();

    $this->artisan('app:check-shippings-coherence')
        ->expectsOutputToContain('ÉCHEC : 1 tarif(s)')
        ->assertExitCode(1);

    expect(Shipping::count())->toBe(2)->and($coherent->exists)->toBeTrue();
});

it('succeeds when every shipping is coherent', function () {
    Shipping::factory()->count(3)->create();

    $this->artisan('app:check-shippings-coherence')
        ->expectsOutputToContain('OK')
        ->assertExitCode(0);
});
