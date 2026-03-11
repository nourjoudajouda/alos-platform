<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-24 — Tenant (Law Firm) fields for SaaS registration flow.
     * Maps to LAWFIRM schema: subdomain, managing_partner, subscription_plan_id,
     * status, user_limit, lawyer_limit, storage_limit, start_date, end_date.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('subdomain', 50)->nullable()->after('domain');
            $table->string('managing_partner', 100)->nullable()->after('email');
            $table->unsignedBigInteger('subscription_plan_id')->nullable()->after('plan');
            $table->string('status', 20)->default('active')->after('subscription_plan_id');
            $table->unsignedInteger('user_limit')->default(10)->after('status');
            $table->unsignedInteger('lawyer_limit')->default(5)->after('user_limit');
            $table->unsignedInteger('storage_limit')->default(1024)->after('lawyer_limit'); // MB
            $table->date('start_date')->nullable()->after('storage_limit');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'subdomain',
                'managing_partner',
                'subscription_plan_id',
                'status',
                'user_limit',
                'lawyer_limit',
                'storage_limit',
                'start_date',
                'end_date',
            ]);
        });
    }
};
