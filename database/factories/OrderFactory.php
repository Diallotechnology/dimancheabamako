<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => rand(1, 10),
            'country_id' => rand(1, 10),
            'transport_id' => rand(1, 2),
            'reference' => uniqid(),
            'adresse' => fake()->streetAddress(),
            'postal' => fake()->postcode(),
            'ville' => fake()->city(),
            // 'pays' => fake()->country(),
            'payment' => fake()->randomElement(['Mastercard', 'Visa', 'Paypal']),
        ];
    }
}
