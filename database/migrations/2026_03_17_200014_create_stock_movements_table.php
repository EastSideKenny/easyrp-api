<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['sale', 'manual_adjustment']);
            $table->integer('quantity_change');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
            $table->index('created_at');
        });
    }
    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
