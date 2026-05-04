<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('free_trial_used_at')->nullable()->after('trial_ends_at');
        });

        // Anyone who was given a signup trial window can’t start a second timed trial via the API.
        DB::table('tenants')->whereNotNull('trial_ends_at')->update([
            'free_trial_used_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('free_trial_used_at');
        });
    }
};
