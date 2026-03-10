<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * case_number = full code (e.g. DE-001), case_number_suffix = numeric part only (e.g. 001).
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('case_number_suffix', 16)->nullable()->after('case_number');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('case_number_suffix');
        });
    }
};
