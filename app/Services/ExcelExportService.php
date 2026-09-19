<?php

namespace App\Services;

use App\Models\GeneratedReport;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    /**
     * Generate a styled Microsoft Excel (.xlsx) workbook for a single hourly report snapshot.
     */
    public function generateSingleReportExcel(GeneratedReport $report): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Report #{$report->id}");

        $dateStr = $report->report_date->format('Y-m-d');
        $dateFormatted = $report->report_date->format('F d, Y');
        $formattedHour = sprintf('%02d:00', $report->report_hour);

        // 1. Report Title Header
        $sheet->setCellValue('A1', "HOURLY SALES REPORT — SNAPSHOT #{$report->id}");
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 2. Metadata Subtitle
        $sheet->setCellValue('A2', "Date: {$dateFormatted} | Hour: {$formattedHour} (PHT) | Generated: {$report->generated_at->format('Y-m-d H:i:s')} | Status: " . strtoupper($report->status));
        $sheet->mergeCells('A2:N2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));
        $sheet->getRowDimension(2)->setRowHeight(20);

        // 3. Table Column Headers (Row 4)
        $headers = [
            'A4' => 'Report ID',
            'B4' => 'Report Date',
            'C4' => 'Reporting Hour',
            'D4' => 'Classification',
            'E4' => 'Platform',
            'F4' => 'Storefront Name',
            'G4' => 'Shop Code',
            'H4' => 'Orders',
            'I4' => 'Units Sold',
            'J4' => 'Gross Sales (PHP)',
            'K4' => 'Discounts (PHP)',
            'L4' => 'Refunds (PHP)',
            'M4' => 'Net Sales (PHP)',
            'N4' => 'Generated At (PHT)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $headerRange = 'A4:N4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Alignments for headers
        $sheet->getStyle('A4:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E4:G4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H4:M4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 5;

        // 4. Storefront Rows
        if (!empty($report->report_data['shops'])) {
            foreach ($report->report_data['shops'] as $shop) {
                $sheet->setCellValue("A{$row}", $report->id);
                $sheet->setCellValueExplicit("B{$row}", $dateStr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C{$row}", $formattedHour, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("D{$row}", 'Storefront');
                $sheet->setCellValue("E{$row}", $shop['platform_name'] ?? 'N/A');
                $sheet->setCellValue("F{$row}", $shop['shop_name'] ?? 'N/A');
                $sheet->setCellValue("G{$row}", $shop['shop_code'] ?? 'N/A');
                $sheet->setCellValue("H{$row}", (int)($shop['orders'] ?? 0));
                $sheet->setCellValue("I{$row}", (int)($shop['units_sold'] ?? 0));
                $sheet->setCellValue("J{$row}", (float)($shop['gross_sales'] ?? 0));
                $sheet->setCellValue("K{$row}", (float)($shop['discounts'] ?? 0));
                $sheet->setCellValue("L{$row}", (float)($shop['refunds'] ?? 0));
                $sheet->setCellValue("M{$row}", (float)($shop['net_sales'] ?? 0));
                $sheet->setCellValueExplicit("N{$row}", $report->generated_at->format('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // Alternating row color
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                }

                $sheet->getRowDimension($row)->setRowHeight(20);
                $row++;
            }
        }

        // 5. Platform Subtotal Rows
        if (!empty($report->report_data['platforms'])) {
            foreach ($report->report_data['platforms'] as $platform) {
                $sheet->setCellValue("A{$row}", $report->id);
                $sheet->setCellValueExplicit("B{$row}", $dateStr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("C{$row}", $formattedHour, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("D{$row}", 'Platform Subtotal');
                $sheet->setCellValue("E{$row}", $platform['name'] ?? 'N/A');
                $sheet->setCellValue("F{$row}", "Subtotal: " . ($platform['name'] ?? 'Platform'));
                $sheet->setCellValue("G{$row}", $platform['code'] ?? 'N/A');
                $sheet->setCellValue("H{$row}", (int)($platform['orders'] ?? 0));
                $sheet->setCellValue("I{$row}", (int)($platform['units_sold'] ?? 0));
                $sheet->setCellValue("J{$row}", (float)($platform['gross_sales'] ?? 0));
                $sheet->setCellValue("K{$row}", (float)($platform['discounts'] ?? 0));
                $sheet->setCellValue("L{$row}", (float)($platform['refunds'] ?? 0));
                $sheet->setCellValue("M{$row}", (float)($platform['net_sales'] ?? 0));
                $sheet->setCellValueExplicit("N{$row}", $report->generated_at->format('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
                $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getRowDimension($row)->setRowHeight(22);
                $row++;
            }
        }

        // 6. Consolidated Grand Total Row
        $sheet->setCellValue("A{$row}", $report->id);
        $sheet->setCellValueExplicit("B{$row}", $dateStr, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("C{$row}", $formattedHour, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue("D{$row}", 'GRAND TOTAL');
        $sheet->setCellValue("E{$row}", 'ALL PLATFORMS');
        $sheet->setCellValue("F{$row}", 'Consolidated Grand Total (All Stores)');
        $sheet->setCellValue("G{$row}", 'ALL');
        $sheet->setCellValue("H{$row}", (int)$report->total_orders);
        $sheet->setCellValue("I{$row}", (int)$report->total_units);
        $sheet->setCellValue("J{$row}", (float)$report->gross_sales);
        $sheet->setCellValue("K{$row}", (float)$report->discounts);
        $sheet->setCellValue("L{$row}", (float)$report->refunds);
        $sheet->setCellValue("M{$row}", (float)$report->net_sales);
        $sheet->setCellValueExplicit("N{$row}", $report->generated_at->format('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE2E8F0');
        $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$row}:N{$row}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Apply Number Formats & Alignments across data rows
        $dataEndRow = $row;
        $sheet->getStyle("A5:D{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E5:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("H5:I{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("J5:M{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("N5:N{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Integer format for Orders and Units
        $sheet->getStyle("H5:I{$dataEndRow}")->getNumberFormat()->setFormatCode('#,##0');
        // Currency / Accounting 2-decimal format for Sales
        $sheet->getStyle("J5:M{$dataEndRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00;[Red]("-₱"#,##0.00);"-"');

        // Thin grid borders
        $sheet->getStyle("A4:N{$dataEndRow}")->getBorders()->getInside()->setBorderStyle(Border::BORDER_HAIR);
        $sheet->getStyle("A4:N{$dataEndRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        // 7. CRITICAL: Auto-size all columns so NO '########' occurs
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add extra padding to columns prone to truncation
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(34);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(16);
        $sheet->getColumnDimension('L')->setWidth(16);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(24);

        return $this->outputWorkbook($spreadsheet);
    }

    /**
     * Generate styled Excel (.xlsx) for batch summary reports.
     */
    public function generateBatchSummaryExcel(Collection $reports): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Hourly Summary');

        // Header Row
        $headers = [
            'A1' => 'Report ID',
            'B1' => 'Report Date',
            'C1' => 'Reporting Hour',
            'D1' => 'Formatted Hour',
            'E1' => 'Total Orders',
            'F1' => 'Total Units',
            'G1' => 'Gross Sales (PHP)',
            'H1' => 'Discounts (PHP)',
            'I1' => 'Refunds (PHP)',
            'J1' => 'Net Sales (PHP)',
            'K1' => 'Status',
            'L1' => 'Generated At (PHT)',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A1:L1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getRowDimension(1)->setRowHeight(26);

        $row = 2;
        foreach ($reports as $r) {
            $sheet->setCellValue("A{$row}", $r->id);
            $sheet->setCellValueExplicit("B{$row}", $r->report_date->format('Y-m-d'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $r->report_hour);
            $sheet->setCellValueExplicit("D{$row}", sprintf('%02d:00', $r->report_hour), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", (int)$r->total_orders);
            $sheet->setCellValue("F{$row}", (int)$r->total_units);
            $sheet->setCellValue("G{$row}", (float)$r->gross_sales);
            $sheet->setCellValue("H{$row}", (float)$r->discounts);
            $sheet->setCellValue("I{$row}", (float)$r->refunds);
            $sheet->setCellValue("J{$row}", (float)$r->net_sales);
            $sheet->setCellValue("K{$row}", strtoupper($r->status));
            $sheet->setCellValueExplicit("L{$row}", $r->generated_at->format('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }
            $row++;
        }

        $lastRow = $row - 1;
        $sheet->getStyle("E2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G2:J{$lastRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00;[Red]("-₱"#,##0.00);"-"');

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(18);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(16);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(24);

        return $this->outputWorkbook($spreadsheet);
    }

    /**
     * Generate styled Excel (.xlsx) for batch detailed stores reports.
     */
    public function generateBatchDetailedExcel(Collection $reports): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detailed Store Reports');

        $headers = [
            'A1' => 'Report ID',
            'B1' => 'Report Date',
            'C1' => 'Reporting Hour',
            'D1' => 'Formatted Hour',
            'E1' => 'Platform',
            'F1' => 'Storefront Name',
            'G1' => 'Shop Code',
            'H1' => 'Orders',
            'I1' => 'Units Sold',
            'J1' => 'Gross Sales (PHP)',
            'K1' => 'Discounts (PHP)',
            'L1' => 'Refunds (PHP)',
            'M1' => 'Net Sales (PHP)',
            'N1' => 'Generated At (PHT)',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $sheet->getStyle('A1:N1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $sheet->getRowDimension(1)->setRowHeight(26);

        $row = 2;
        foreach ($reports as $r) {
            $shops = $r->report_data['shops'] ?? [];
            foreach ($shops as $s) {
                $sheet->setCellValue("A{$row}", $r->id);
                $sheet->setCellValueExplicit("B{$row}", $r->report_date->format('Y-m-d'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("C{$row}", $r->report_hour);
                $sheet->setCellValueExplicit("D{$row}", sprintf('%02d:00', $r->report_hour), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValue("E{$row}", $s['platform_name'] ?? 'N/A');
                $sheet->setCellValue("F{$row}", $s['shop_name'] ?? 'N/A');
                $sheet->setCellValue("G{$row}", $s['shop_code'] ?? 'N/A');
                $sheet->setCellValue("H{$row}", (int)($s['orders'] ?? 0));
                $sheet->setCellValue("I{$row}", (int)($s['units_sold'] ?? 0));
                $sheet->setCellValue("J{$row}", (float)($s['gross_sales'] ?? 0));
                $sheet->setCellValue("K{$row}", (float)($s['discounts'] ?? 0));
                $sheet->setCellValue("L{$row}", (float)($s['refunds'] ?? 0));
                $sheet->setCellValue("M{$row}", (float)($s['net_sales'] ?? 0));
                $sheet->setCellValueExplicit("N{$row}", $r->generated_at->format('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:N{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
                }
                $row++;
            }
        }

        $lastRow = $row - 1;
        $sheet->getStyle("H2:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("J2:M{$lastRow}")->getNumberFormat()->setFormatCode('"₱"#,##0.00;[Red]("-₱"#,##0.00);"-"');

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(34);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(16);
        $sheet->getColumnDimension('L')->setWidth(16);
        $sheet->getColumnDimension('M')->setWidth(18);
        $sheet->getColumnDimension('N')->setWidth(24);

        return $this->outputWorkbook($spreadsheet);
    }

    protected function outputWorkbook(Spreadsheet $spreadsheet): string
    {
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content ?: '';
    }
}
