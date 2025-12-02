<?php

declare(strict_types=1);

namespace Database\Factories;

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
            'transport_id' => rand(1, 2),
            'zone_id' => rand(1, 2),
            'poids_id' => rand(1, 2),
            'temps' => $this->faker->randomElement(['2 jours', '3 jours', '4 jours']),
        ];
    }
}
