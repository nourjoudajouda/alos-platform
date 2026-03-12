<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-29 — Subscription Plans (schema from SUBSCRIPTIONPLAN: plan_id, plan_name, price, user_limit, lawyer_limit, storage_limit, features_json, created_at).
     */
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 100);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('user_limit');
            $table->unsignedInteger('lawyer_limit');
            $table->unsignedInteger('storage_limit'); // MB
            $table->json('features_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
