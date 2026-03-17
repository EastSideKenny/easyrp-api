<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('stripe_subscription_id')->nullable();
            $table->enum('status', ['active', 'trialing', 'canceled', 'past_due'])->default('trialing');
            $table->timestamp('current_period_end')->nullable();
            $table->timestamps();
            $table->index('tenant_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('tenant_subscriptions');
    }
};
