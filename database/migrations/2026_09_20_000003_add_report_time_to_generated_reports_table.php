<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('generated_reports', function (Blueprint $table) {
            $table->string('report_time', 10)->nullable()->after('report_hour');
            $table->index(['report_date', 'report_time']);
        });

        // Backfill report_time for existing snapshots
        $existing = DB::table('generated_reports')->get();
        foreach ($existing as $report) {
            $formatted = sprintf('%02d:00', $report->report_hour);
            DB::table('generated_reports')->where('id', $report->id)->update([
                'report_time' => $formatted,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_reports', function (Blueprint $table) {
            $table->dropIndex(['report_date', 'report_time']);
            $table->dropColumn('report_time');
        });
    }
};
