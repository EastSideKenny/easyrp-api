<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        $slug = Str::slug($name) . '-' . fake()->unique()->numerify('####');

        return [
            'name' => $name,
            'slug' => $slug,
            'subdomain' => $slug,
            'plan_id' => null,
            'is_active' => true,
        ];
    }
}
