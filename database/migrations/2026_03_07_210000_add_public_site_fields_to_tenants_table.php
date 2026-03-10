<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-20 — حقول الموقع الخارجي للتيننت: شعار، نبذة، بريد، هاتف، مدينة
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo', 500)->nullable()->after('public_site_enabled');
            $table->text('description')->nullable()->after('logo');
            $table->string('email', 255)->nullable()->after('description');
            $table->string('phone', 64)->nullable()->after('email');
            $table->string('city', 128)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['logo', 'description', 'email', 'phone', 'city']);
        });
    }
};
