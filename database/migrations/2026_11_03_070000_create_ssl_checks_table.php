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
        Schema::create('ssl_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // valid, expiring, expired, error
            $table->string('issuer')->nullable();
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            $table->integer('days_remaining')->nullable();
            $table->string('fingerprint_sha256', 64)->nullable();
            $table->json('chain_details')->nullable();
            $table->text('error_message')->nullable();
            $table->dateTime('checked_at')->nullable();
            $table->timestamps();

            $table->index(['domain_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssl_checks');
    }
};
