<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * ALOS-S1-29B — Tenant subscription: contract_start_date, contract_end_date, billing_cycle, plan_price.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->date('contract_start_date')->nullable()->after('end_date');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->string('billing_cycle', 20)->nullable()->after('contract_end_date'); // monthly, yearly
            $table->decimal('plan_price', 10, 2)->nullable()->after('billing_cycle');
        });

        // Backfill from start_date/end_date for existing rows
        $tenants = \DB::table('tenants')->select('id', 'start_date', 'end_date')->get();
        foreach ($tenants as $t) {
            $updates = [];
            if ($t->start_date !== null) {
                $updates['contract_start_date'] = $t->start_date;
            }
            if ($t->end_date !== null) {
                $updates['contract_end_date'] = $t->end_date;
            }
            if ($updates !== []) {
                \DB::table('tenants')->where('id', $t->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['contract_start_date', 'contract_end_date', 'billing_cycle', 'plan_price']);
        });
    }
};
