<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Features
        $features = [
            ['code' => 'invoices',   'name' => 'Invoices',   'description' => 'Create and manage invoices'],
            ['code' => 'products',   'name' => 'Products',   'description' => 'Manage product catalogue and stock'],
            ['code' => 'customers',  'name' => 'Customers',  'description' => 'Manage customer records'],
            ['code' => 'orders',     'name' => 'Orders',     'description' => 'Process and track orders'],
            ['code' => 'storefront', 'name' => 'Storefront', 'description' => 'Online webshop / storefront'],
            ['code' => 'reports',    'name' => 'Reports',    'description' => 'Advanced reporting and analytics'],
            ['code' => 'payments',   'name' => 'Payments',   'description' => 'Record and track payments'],
            ['code' => 'inventory',  'name' => 'Inventory',  'description' => 'Stock movements and inventory management'],
            ['code' => 'offers',     'name' => 'Offers',     'description' => 'Create and send quotes / offers to customers'],
        ];

        foreach ($features as $feature) {
            Feature::firstOrCreate(['code' => $feature['code']], $feature);
        }

        // Plans
        // features key: [ feature_code => limit (null = unlimited) ]
        $plans = [
            [
                'name'          => 'Free Trial',
                'slug'          => 'free_trial',
                'price_monthly' => 0.00,
                'price_yearly'  => 0.00,
                'is_active'     => true,
                'features' => [
                    'invoices'  => 25,
                    'products'  => 25,
                    'customers' => 25,
                    'orders'    => 25,
                    'offers'    => 25,
                    'payments'  => null,
                    'inventory' => null,
                ],
            ],
            [
                'name'          => 'Starter',
                'slug'          => 'starter',
                'price_monthly' => 19.00,
                'price_yearly'  => 190.00,
                'is_active'     => true,
                'features' => [
                    'invoices'  => 250,
                    'products'  => 250,
                    'customers' => 250,
                    'orders'    => 250,
                    'offers'    => 250,
                    'payments'  => null,
                    'inventory' => null,
                ],
            ],
            [
                'name'          => 'Pro',
                'slug'          => 'pro',
                'price_monthly' => 49.00,
                'price_yearly'  => 490.00,
                'is_active'     => true,
                'features' => [
                    'invoices'   => null,
                    'products'   => null,
                    'customers'  => null,
                    'orders'     => null,
                    'offers'     => null,
                    'payments'   => null,
                    'inventory'  => null,
                    'storefront' => null,
                    'reports'    => null,
                ],
            ],
            [
                'name'          => 'Exclusive',
                'slug'          => 'exclusive',
                'price_monthly' => 0.00,
                'price_yearly'  => 0.00,
                'is_active'     => false,
                'features' => [
                    'invoices'   => null,
                    'products'   => null,
                    'customers'  => null,
                    'orders'     => null,
                    'offers'     => null,
                    'payments'   => null,
                    'inventory'  => null,
                    'storefront' => null,
                    'reports'    => null,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $featureMap = $planData['features'];
            unset($planData['features']);

            $plan = Plan::firstOrCreate(['slug' => $planData['slug']], $planData);

            // Build sync payload: [ feature_id => ['limit' => value] ]
            $syncData = [];
            foreach ($featureMap as $code => $limit) {
                $feature = Feature::where('code', $code)->first();
                if ($feature) {
                    $syncData[$feature->id] = ['limit' => $limit];
                }
            }

            $plan->features()->sync($syncData);
        }
    }
}
