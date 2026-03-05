<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-08 — Client Portal: link user to client, portal permission, account status.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->after('tenant_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->string('portal_permission', 32)->nullable()->after('client_id');
            $table->boolean('portal_active')->default(true)->after('portal_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['portal_permission', 'portal_active']);
        });
    }
};
