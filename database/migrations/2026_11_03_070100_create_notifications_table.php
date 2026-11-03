<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // h30, h14, h7, expired
            $table->string('channel')->default('whatsapp');
            $table->string('recipient')->nullable();
            $table->string('status')->default('sent'); // sent, failed, pending
            $table->string('fingerprint_sha256', 64)->nullable();
            $table->json('payload')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            // Idempotency: avoid sending duplicate notification of the same type for the same certificate fingerprint
            $table->unique(['domain_id', 'type', 'fingerprint_sha256'], 'unique_domain_notification_idempotency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
