<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'price_monthly' => fake()->randomFloat(2, 5, 100),
            'price_yearly' => fake()->randomFloat(2, 50, 1000),
            'is_active' => true,
        ];
    }
}
