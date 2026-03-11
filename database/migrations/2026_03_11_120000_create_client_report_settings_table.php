<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-15.1 — Client Report Settings: per-client report preferences.
     */
    public function up(): void
    {
        Schema::create('client_report_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->boolean('case_status_enabled')->default(true);
            $table->boolean('activity_summary_enabled')->default(true);
            $table->boolean('new_documents_enabled')->default(true);
            $table->string('delivery_channel', 32)->default('both'); // in_app | email | both
            $table->string('frequency', 32)->default('weekly'); // weekly | monthly | major_update
            $table->boolean('send_to_client')->default(true);
            $table->boolean('send_to_responsible_lawyer')->default(true);
            $table->boolean('send_to_office_management')->default(false);
            $table->timestamps();

            $table->unique(['client_id']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_report_settings');
    }
};
