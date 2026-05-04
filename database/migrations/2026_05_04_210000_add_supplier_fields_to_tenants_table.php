<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('supplier_address_line_1')->nullable()->after('theme');
            $table->string('supplier_address_line_2')->nullable()->after('supplier_address_line_1');
            $table->string('supplier_city')->nullable()->after('supplier_address_line_2');
            $table->string('supplier_postal_code')->nullable()->after('supplier_city');
            $table->string('supplier_country')->nullable()->after('supplier_postal_code');
            $table->string('supplier_vat_number')->nullable()->after('supplier_country');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_address_line_1',
                'supplier_address_line_2',
                'supplier_city',
                'supplier_postal_code',
                'supplier_country',
                'supplier_vat_number',
            ]);
        });
    }
};
