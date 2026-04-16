<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'type' => fake()->randomElement(['physical', 'service']),
            'price' => fake()->randomFloat(2, 1, 500),
            'cost_price' => fake()->randomFloat(2, 0.5, 200),
            'tax_rate' => fake()->randomFloat(2, 0, 25),
            'track_inventory' => true,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'low_stock_threshold' => 5,
            'is_active' => true,
        ];
    }
}
