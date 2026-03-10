<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-12 — Case Sessions & Calendar (Court Hearings).
     */
    public function up(): void
    {
        Schema::create('case_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->string('court_name')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('scheduled'); // scheduled | completed | cancelled | postponed
            $table->timestamps();

            $table->index(['case_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_sessions');
    }
};
