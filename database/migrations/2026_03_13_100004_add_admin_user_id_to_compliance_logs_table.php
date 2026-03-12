<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-38 — Compliance logs: link platform security events to admin (e.g. failed admin login).
     */
    public function up(): void
    {
        Schema::table('compliance_logs', function (Blueprint $table) {
            $table->foreignId('admin_user_id')->nullable()->after('user_id')->constrained('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compliance_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('admin_user_id');
        });
    }
};
