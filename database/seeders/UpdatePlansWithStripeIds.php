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
        Plan::where('slug', 'starter')->update([
            'stripe_product_id' => 'prod_URwczrTNMocYiP',
            'stripe_price_monthly_id' => 'price_1TT2jSLIqjurDXdCDnPLBrlM',
            'stripe_price_yearly_id' => 'price_1TT2jSLIqjurDXdCBsaDKB9n',
        ]);

        Plan::where('slug', 'pro')->update([
            'stripe_product_id' => 'prod_US991j1UbIH22u',
            'stripe_price_monthly_id' => 'price_1TTErTLIqjurDXdCm60ayfAT',
            'stripe_price_yearly_id' => 'price_1TTErqLIqjurDXdCGHVupZ2d',
        ]);
    }
}
