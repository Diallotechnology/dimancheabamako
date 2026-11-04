<?php

declare(strict_types=1);

use App\Models\Country;
use App\Models\Poids;
use App\Models\Shipping;
use Database\Seeders\DatabaseSeeder;

it('seeds a delivery setup where every country has carriers and a rate for each weight range', function () {
    $this->seed(DatabaseSeeder::class);

    $countries = Country::with('zone.transports')->get();
    expect($countries)->not->toBeEmpty();

    foreach ($countries as $country) {
        expect($country->zone->transports)->not->toBeEmpty("{$country->nom} has no carrier");

        foreach ($country->zone->transports as $transport) {
            foreach (Poids::all() as $poids) {
                expect(Shipping::where('zone_id', $country->zone_id)
                    ->where('transport_id', $transport->id)
                    ->where('poids_id', $poids->id)
                    ->exists())->toBeTrue("no rate for {$transport->nom} / {$poids->min}-{$poids->max} kg");
            }
        }
    }
});
