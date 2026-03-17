<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('subdomain');
            $table->string('team_size')->nullable()->after('industry');
            $table->json('modules')->nullable()->after('team_size');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['industry', 'team_size', 'modules']);
        });
    }
};
