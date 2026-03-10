<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-13 — Log sent reminders to avoid duplicates.
     */
    public function up(): void
    {
        Schema::create('session_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_session_id')->constrained('case_sessions')->cascadeOnDelete();
            $table->foreignId('reminder_rule_id')->constrained('reminder_rules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_type', 32)->nullable(); // lawyer | team | client
            $table->timestamp('sent_at');

            $table->unique(['case_session_id', 'reminder_rule_id', 'user_id'], 'sess_rem_log_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_reminder_logs');
    }
};
