<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cashier 16 writes `meter_id` and `meter_event_name` on every subscription_items insert
     * for metered billing support. Older databases created before those columns existed
     * need them added here.
     */
    public function up(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_items', 'meter_id')) {
                $table->string('meter_id')->nullable()->after('stripe_price');
            }
            if (! Schema::hasColumn('subscription_items', 'meter_event_name')) {
                $table->string('meter_event_name')->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            if (Schema::hasColumn('subscription_items', 'meter_event_name')) {
                $table->dropColumn('meter_event_name');
            }
            if (Schema::hasColumn('subscription_items', 'meter_id')) {
                $table->dropColumn('meter_id');
            }
        });
    }
};
