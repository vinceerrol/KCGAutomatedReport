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
        Schema::create('hourly_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->date('report_date');
            $table->unsignedTinyInteger('hour'); // 0-23
            $table->integer('orders')->default(0);
            $table->integer('units_sold')->default(0);
            $table->decimal('gross_sales', 12, 2)->default(0.00);
            $table->decimal('discounts', 12, 2)->default(0.00);
            $table->decimal('refunds', 12, 2)->default(0.00);
            $table->decimal('net_sales', 12, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['shop_id', 'report_date', 'hour']);
            $table->index(['report_date', 'hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hourly_metrics');
    }
};
