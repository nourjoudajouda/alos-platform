<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-37 — IP Restriction structure for admin access.
     * admin_user_id null = global rule; otherwise per-admin whitelist.
     */
    public function up(): void
    {
        Schema::create('admin_ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('ip_address', 50);
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();

            $table->index(['admin_user_id', 'status']);
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_ip_whitelist');
    }
};
