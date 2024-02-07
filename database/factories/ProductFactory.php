<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categorie_id' => rand(1, 15),
            'reference' => uniqid(),
            'nom' => $this->faker->sentence(),
            'prix' => rand(15000, 50000),
            'poids' => rand(100, 500),
            'stock' => rand(15, 50),
            'color' => $this->faker->colorName(),
            'taille' => rand(1, 5),
            'description' => $this->faker->paragraph(),
            'cover' => $this->faker->imageUrl(),
        ];
    }
}
