<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-01 — Add tenant_id to users, smart backfill existing users to default tenant.
     */
    public function up(): void
    {
        // Ensure default tenant exists for backfill
        if (Schema::hasTable('tenants') && ! DB::table('tenants')->where('id', 1)->exists()) {
            DB::table('tenants')->insert([
                'id' => 1,
                'name' => 'Default',
                'slug' => 'default',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')
                ->after('id')
                ->default(1)
                ->constrained('tenants')
                ->cascadeOnDelete();
        });

        // Smart backfill: ensure any existing row has tenant_id (e.g. SQLite)
        DB::table('users')->whereNull('tenant_id')->update(['tenant_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }
};
