<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول الأدمن (SYSTEMADMIN) — مصادقة لوحة الإدارة منفصلة عن جدول users.
     * الحقول: id, name, email, password, role, created_at (Laravel default PK).
     */
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password', 100);
            $table->string('role', 50);
            $table->rememberToken(); // للتوافق مع Laravel Auth
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable(); // للتوافق مع Eloquent
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
