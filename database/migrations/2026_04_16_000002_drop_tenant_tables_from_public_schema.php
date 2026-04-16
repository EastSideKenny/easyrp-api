<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These tables have been moved to per-tenant schemas.
        // Drop them from the public schema (reverse FK order).
        Schema::dropIfExists('setup_progress');
        Schema::dropIfExists('webshop_settings');
        Schema::dropIfExists('offer_items');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('product_category_product');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }

    public function down(): void
    {
        // Re-creation is handled by the archived migrations if needed.
    }
};
