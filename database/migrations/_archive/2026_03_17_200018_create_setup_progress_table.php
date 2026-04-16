<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('setup_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('step');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->index('tenant_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('setup_progress');
    }
};
