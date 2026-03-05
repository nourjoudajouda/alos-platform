<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-09 — Secure Messaging: threads per client (optional case/consultation link).
     */
    public function up(): void
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('case_id')->nullable();
            $table->unsignedBigInteger('consultation_id')->nullable();
            $table->string('subject');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_threads');
    }
};
