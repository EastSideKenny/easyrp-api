<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class UpdatePlansWithStripeIds extends Seeder
{
    /**
     * Seed the application's database with Stripe IDs.
     */
    public function run(): void
    {
        $stripePlanConfig = config('services.stripe.plans', []);

        foreach ($stripePlanConfig as $slug => $ids) {
            $productId = $ids['product_id'] ?? null;
            $monthlyPriceId = $ids['monthly_price_id'] ?? null;
            $yearlyPriceId = $ids['yearly_price_id'] ?? null;

            if (!$productId || !$monthlyPriceId || !$yearlyPriceId) {
                continue;
            }

            Plan::where('slug', $slug)->update([
                'stripe_product_id' => $productId,
                'stripe_price_monthly_id' => $monthlyPriceId,
                'stripe_price_yearly_id' => $yearlyPriceId,
            ]);
        }
    }
}
