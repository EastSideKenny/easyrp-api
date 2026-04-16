<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offer_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // 'accepted' or 'declined'
            $table->string('channel'); // 'email' or 'portal'
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_email')->nullable();
            $table->timestamp('responded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_responses');
    }
};
