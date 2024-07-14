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
            'categorie_id' => rand(1, 5),
            'reference' => uniqid(),
            'nom' => $this->faker->sentence(1),
            'prix' => rand(15000, 50000),
            'poids' => rand(1, 5),
            'stock' => rand(15, 50),
            'color' => $this->faker->colorName(),
            'favoris' => $this->faker->boolean(\false),
            'taille' => rand(1, 5),
            'resume' => $this->faker->paragraph(),
            'description' => $this->faker->paragraph(3),
            'cover' => $this->faker->imageUrl(),
        ];
    }
}
