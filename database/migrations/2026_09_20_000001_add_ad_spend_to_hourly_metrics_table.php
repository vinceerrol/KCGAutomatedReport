<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hourly_metrics', function (Blueprint $table) {
            $table->decimal('ad_spend', 12, 2)->default(0.00)->after('units_sold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hourly_metrics', function (Blueprint $table) {
            $table->dropColumn('ad_spend');
        });
    }
};
