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
        Schema::create('generated_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->unsignedTinyInteger('report_hour'); // 0-23
            $table->integer('total_orders')->default(0);
            $table->integer('total_units')->default(0);
            $table->decimal('gross_sales', 12, 2)->default(0.00);
            $table->decimal('discounts', 12, 2)->default(0.00);
            $table->decimal('refunds', 12, 2)->default(0.00);
            $table->decimal('net_sales', 12, 2)->default(0.00);
            $table->json('report_data')->nullable(); // detailed platform and shop breakdown
            $table->timestamp('generated_at')->useCurrent();
            $table->string('status')->default('completed'); // completed, failed
            $table->timestamps();

            $table->index(['report_date', 'report_hour']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_reports');
    }
};
