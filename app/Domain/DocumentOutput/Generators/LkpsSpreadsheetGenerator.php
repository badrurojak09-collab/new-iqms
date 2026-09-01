<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Generators;

use App\Domain\DocumentOutput\Adapters\BanPtIaptDocumentAdapter;
use App\Domain\DocumentOutput\Adapters\LamInfokom21DocumentAdapter;
use App\Domain\DocumentOutput\Services\AccreditationDocumentExporter;
use App\Models\Accreditation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Generator LKPS/LKPT Excel (.xlsx) native.
 *
 * Menghasilkan file .xlsx terstruktur sesuai blanko resmi dengan:
 * - Cover sheet (metadata akreditasi)
 * - Satu worksheet per tabel LKPS/LKPT
 * - Header berwarna, border, dan auto-width kolom
 */
final class LkpsSpreadsheetGenerator
{
    // Header row background color (deep blue)
    private const HEADER_BG = '1E3A5F';
    // Header font color (white)
    private const HEADER_FG = 'FFFFFF';
    // Alternating row color
    private const ROW_ALT_BG = 'EFF4FB';
    // Accent color for cover
    private const COVER_ACCENT = '0F52A0';

    public function __construct(
        private readonly AccreditationDocumentExporter $exporter
    ) {}

    public function generate(Accreditation $accreditation): string
    {
        $adapter = $this->exporter->resolveAdapter($accreditation);
        $data = $adapter->buildLkpsData($accreditation);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Antigravity i-QMS')
            ->setTitle($data['type'] . ' - ' . $accreditation->code)
            ->setSubject('Laporan Kinerja Program Studi / Perguruan Tinggi')
            ->setDescription('Dokumen luaran akreditasi yang digenerate otomatis oleh i-QMS.');

        // Cover sheet
        $this->buildCoverSheet($spreadsheet->getActiveSheet(), $data, $accreditation);

        // One sheet per table
        foreach ($data['tables'] as $table) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($this->sheetTitle($table['code']));
            $this->buildTableSheet($sheet, $table);
        }

        // Remove default sheet if cover was built on it
        $spreadsheet->setActiveSheetIndex(0);

        $tmpFile = tempnam(sys_get_temp_dir(), 'lkps_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return (string) $content;
    }

    private function buildCoverSheet(Worksheet $sheet, array $data, Accreditation $accreditation): void
    {
        $sheet->setTitle('Cover');

        // Merge header area
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');
        $sheet->mergeCells('A4:F4');
        $sheet->mergeCells('A5:F5');
        $sheet->mergeCells('A6:F6');

        $coverText = strtoupper($data['type']);
        $sheet->setCellValue('A1', $coverText);
        $sheet->setCellValue('A2', $data['institution_name'] ?? '');
        $sheet->setCellValue('A3', isset($data['study_program_name']) ? 'Program Studi: ' . $data['study_program_name'] : '');
        $sheet->setCellValue('A4', 'Kode Kegiatan: ' . $data['accreditation_code']);
        $sheet->setCellValue('A5', 'Digenerate pada: ' . $data['generated_at']);
        $sheet->setCellValue('A6', 'Sistem: Antigravity i-QMS — Dokumen digenerate otomatis');

        // Style title row
        $this->applyStyle($sheet, 'A1:F1', [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COVER_ACCENT]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // Style sub rows
        foreach (['A2:F2', 'A3:F3'] as $range) {
            $this->applyStyle($sheet, $range, [
                'font' => ['bold' => true, 'size' => 13],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
        foreach (['A4:F4', 'A5:F5', 'A6:F6'] as $range) {
            $this->applyStyle($sheet, $range, [
                'font' => ['size' => 10, 'color' => ['rgb' => '64748B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // Table of contents
        $row = 9;
        $sheet->setCellValue("A{$row}", 'Daftar Tabel / Sheets:');
        $this->applyStyle($sheet, "A{$row}:F{$row}", ['font' => ['bold' => true, 'size' => 11]]);
        $row++;

        foreach (($data['tables'] ?? []) as $index => $table) {
            $sheet->setCellValue("A{$row}", ($index + 1) . '. ' . $table['code'] . ': ' . $table['title']);
            $sheet->setCellValue("B{$row}", $table['description'] ?? '');
            $this->applyStyle($sheet, "A{$row}:F{$row}", [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => ($row % 2 === 0) ? self::ROW_ALT_BG : 'FFFFFF']],
                'font' => ['size' => 10],
                'alignment' => ['wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
            ]);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(60);
    }

    private function buildTableSheet(Worksheet $sheet, array $table): void
    {
        $row = 1;

        // Table title row
        $colCount = count($table['headers']);
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", $table['code'] . ': ' . $table['title']);
        $this->applyStyle($sheet, "A{$row}:{$lastCol}{$row}", [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COVER_ACCENT]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(30);
        $row++;

        // Description row
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", $table['description']);
        $this->applyStyle($sheet, "A{$row}:{$lastCol}{$row}", [
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '64748B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'wrapText' => true],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        // Header row
        foreach ($table['headers'] as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue("{$colLetter}{$row}", $header);
        }
        $this->applyStyle($sheet, "A{$row}:{$lastCol}{$row}", [
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => self::HEADER_FG]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(25);
        $row++;

        // Data rows
        foreach ($table['rows'] as $rowIndex => $cells) {
            $isAlt = $rowIndex % 2 !== 0;
            foreach ($cells as $colIndex => $cell) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue("{$colLetter}{$row}", $cell);
            }
            $this->applyStyle($sheet, "A{$row}:{$lastCol}{$row}", [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $isAlt ? self::ROW_ALT_BG : 'FFFFFF']],
                'font' => ['size' => 10],
                'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
            $row++;
        }

        // Auto-fit column widths
        for ($i = 1; $i <= $colCount; $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze panes: freeze header
        $sheet->freezePane('A4');

        // Auto filter on header row
        $sheet->setAutoFilter("A3:{$lastCol}3");
    }

    private function sheetTitle(string $code): string
    {
        // Excel sheet title max 31 chars, strip special chars
        return substr(preg_replace('/[\/\\\\?\*\[\]:]/', '-', $code), 0, 31);
    }

    private function applyStyle(Worksheet $sheet, string $range, array $styles): void
    {
        $styleObj = $sheet->getStyle($range);

        if (isset($styles['font'])) {
            $fontStyles = $styles['font'];
            $font = $styleObj->getFont();
            if (isset($fontStyles['bold'])) $font->setBold($fontStyles['bold']);
            if (isset($fontStyles['italic'])) $font->setItalic($fontStyles['italic']);
            if (isset($fontStyles['size'])) $font->setSize($fontStyles['size']);
            if (isset($fontStyles['color']['rgb'])) $font->getColor()->setRGB($fontStyles['color']['rgb']);
        }

        if (isset($styles['fill'])) {
            $fillStyles = $styles['fill'];
            $fill = $styleObj->getFill();
            if (isset($fillStyles['fillType'])) $fill->setFillType($fillStyles['fillType']);
            if (isset($fillStyles['startColor']['rgb'])) $fill->getStartColor()->setRGB($fillStyles['startColor']['rgb']);
        }

        if (isset($styles['alignment'])) {
            $alignStyles = $styles['alignment'];
            $alignment = $styleObj->getAlignment();
            if (isset($alignStyles['horizontal'])) $alignment->setHorizontal($alignStyles['horizontal']);
            if (isset($alignStyles['vertical'])) $alignment->setVertical($alignStyles['vertical']);
            if (isset($alignStyles['wrapText'])) $alignment->setWrapText($alignStyles['wrapText']);
        }

        if (isset($styles['borders'])) {
            $borderStyles = $styles['borders'];
            $borders = $styleObj->getBorders();
            foreach ($borderStyles as $borderPos => $borderOpts) {
                $borderMethod = match ($borderPos) {
                    'allBorders' => fn() => $borders->getAllBorders(),
                    'outline' => fn() => $borders->getOutline(),
                    'bottom' => fn() => $borders->getBottom(),
                    default => fn() => $borders->getAllBorders(),
                };
                $border = $borderMethod();
                if (isset($borderOpts['borderStyle'])) $border->setBorderStyle($borderOpts['borderStyle']);
                if (isset($borderOpts['color']['rgb'])) $border->getColor()->setRGB($borderOpts['color']['rgb']);
            }
        }
    }
}
