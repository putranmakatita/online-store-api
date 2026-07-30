<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
            'name' => fake()->unique()->words(3, true),
            'price' => fake()->randomFloat(2, 10, 5000),
            'inventory' => fake()->numberBetween(20, 300),
        ];
    }
    public function flashSale(): static
    {
        return $this->state(function () {
            return [
                'name' => 'Flash Sale Product',
                'price' => 99.99,
                'inventory' => 100,
            ];
        });
    }
}
