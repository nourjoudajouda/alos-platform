<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * تمييز يوزر التيننت (مكتب) عن العميل (Client) في جدول users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_type', 32)->nullable()->after('client_id')->comment('tenant_staff | client');
        });

        // تعيين القيم للمستخدمين الحاليين
        DB::table('users')->whereNotNull('client_id')->update(['user_type' => 'client']);
        DB::table('users')->whereNotNull('tenant_id')->whereNull('client_id')->update(['user_type' => 'tenant_staff']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
