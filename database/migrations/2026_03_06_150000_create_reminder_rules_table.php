<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-13 — Reminder rules: trigger times (7 days, 24h, 2h), channels (in-app, email), notify client option.
     */
    public function up(): void
    {
        Schema::create('reminder_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 64); // e.g. "7 days before", "24 hours before"
            $table->unsignedInteger('trigger_minutes'); // e.g. 10080 (7*24*60), 1440, 120
            $table->boolean('channel_database')->default(true);
            $table->boolean('channel_mail')->default(true);
            $table->boolean('notify_client')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_rules');
    }
};
