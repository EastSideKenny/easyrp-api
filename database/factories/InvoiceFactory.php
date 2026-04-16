<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 1000);
        $taxTotal = round($subtotal * 0.1, 2);

        return [
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'customer_id' => null,
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'canceled']),
            'issue_date' => fake()->date(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'total' => $subtotal + $taxTotal,
            'currency' => 'USD',
            'created_by' => null,
        ];
    }
}
