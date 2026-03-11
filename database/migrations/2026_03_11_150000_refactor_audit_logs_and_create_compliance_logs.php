<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-25 — Audit Log & Compliance Log: new audit_logs schema + compliance_logs table.
     */
    public function up(): void
    {
        Schema::dropIfExists('audit_logs');

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_type', 32)->nullable(); // internal / client
            $table->string('action_type', 64);
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata_json')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'action_type']);
            $table->index(['tenant_id', 'entity_type']);
        });

        Schema::create('compliance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_type', 32)->nullable();
            $table->string('attempted_action', 128);
            $table->string('target_entity', 64)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['attempted_action', 'target_entity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_logs');
        Schema::dropIfExists('audit_logs');

        // Restore old audit_logs structure (S1-15.9) if needed
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values');
            $table->json('new_values');
            $table->string('ip_address', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tenant_id', 'entity_type', 'entity_id']);
            $table->index(['tenant_id', 'created_at']);
        });
    }
};
