<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-37 — Platform Login Monitoring: admin login attempts (success, failed, logout).
     */
    public function up(): void
    {
        Schema::create('admin_login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('login_time')->useCurrent();
            $table->string('ip_address', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('login_status', 20); // success, failed, logout
            $table->string('email', 100)->nullable(); // for failed attempts when admin not found

            $table->index(['admin_user_id', 'login_time']);
            $table->index(['login_status', 'login_time']);
            $table->index('login_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_logs');
    }
};
