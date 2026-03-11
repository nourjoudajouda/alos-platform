<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-15.2 — Generated Reports Storage: store every generated report for in-app view and audit.
     */
    public function up(): void
    {
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('report_type', 64); // case_status | activity_summary | new_documents
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('title');
            $table->json('payload_json');
            $table->string('status', 32)->default('generated'); // generated | sent | failed
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'client_id', 'report_type']);
            $table->index(['client_id', 'report_type', 'period_start', 'period_end'], 'gen_rep_client_type_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
