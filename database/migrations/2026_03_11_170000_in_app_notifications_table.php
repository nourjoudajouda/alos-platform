<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ALOS-S1-26 — In-App Notifications.
     * Schema per NOTIFICATION table: tenant_id (not law_firm_id), user_id, type, title, message, read_status, created_at.
     */
    public function up(): void
    {
        Schema::dropIfExists('notifications');

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title', 100);
            $table->text('message');
            $table->boolean('read_status')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('data')->nullable(); // link, entity_type, entity_id for "رابط مباشر للكيان المرتبط"
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'read_status', 'created_at']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
