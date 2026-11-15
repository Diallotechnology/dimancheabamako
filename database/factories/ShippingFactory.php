<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Poids;
use App\Models\Shipping;
use App\Models\Transport;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

use function rand;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipping>
 */
final class ShippingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'montant' => rand(15000, 50000),
            'transport_id' => Transport::factory(),
            // Zone tirée parmi celles déjà desservies par le transporteur (pivot transport_zone) ;
            // à défaut, une zone est créée et rattachée à ce transporteur.
            'zone_id' => function (array $attributes): int {
                $transport = Transport::query()->findOrFail($attributes['transport_id']);
                $zone = $transport->zones()->inRandomOrder()->first();

                if ($zone === null) {
                    $zone = Zone::factory()->create();
                    $transport->zones()->attach($zone->id);
                }

                return $zone->id;
            },
            'poids_id' => Poids::factory(),
            'temps' => $this->faker->randomElement(['2 jours', '3 jours', '4 jours']),
        ];
    }

    /**
     * Garde la table pivot cohérente même si transport et zone sont imposés par l'appelant.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Shipping $shipping): void {
            $shipping->transport->zones()->syncWithoutDetaching([$shipping->zone_id]);
        });
    }
}
