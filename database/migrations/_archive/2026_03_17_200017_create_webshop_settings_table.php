<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('webshop_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('store_name')->nullable();
            $table->string('primary_color', 7)->default('#3490dc');
            $table->string('currency', 3)->default('USD');
            $table->boolean('enable_guest_checkout')->default(true);
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('webshop_settings');
    }
};
