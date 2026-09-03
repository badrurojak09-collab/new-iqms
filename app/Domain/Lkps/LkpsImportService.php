<?php

declare(strict_types=1);

namespace App\Domain\Lkps;

use App\Models\Accreditation;
use App\Models\LkpsDataset;
use App\Models\LkpsTemplate;
use App\Models\LkpsTemplateColumn;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class LkpsImportService
{
    public function __construct(
        private readonly LkpsDatasetService $datasetService
    ) {}

    /**
     * Parse file upload (CSV / XLSX) dan lakukan rekonsiliasi kolom terhadap LkpsTemplate.
     *
     * @return array{
     *     headers: array<int, string>,
     *     mapped_columns: array<string, string>,
     *     unmapped_headers: array<int, string>,
     *     raw_rows_count: int,
     *     preview_rows: array<int, array<string, mixed>>,
     *     validation: array{rows: array<int, array<string, mixed>>, errors: array<int, array<string, string>>, summary: array<string, mixed>}
     * }
     */
    public function parseAndReconcile(UploadedFile $file, LkpsTemplate $template): array
    {
        $template->loadMissing('columns');
        $columns = $template->columns->sortBy('sort_order');

        $ext = strtolower($file->getClientOriginalExtension());
        $filePath = $file->getRealPath();

        $rows = [];
        if ($ext === 'csv' || $ext === 'txt') {
            $rows = $this->parseCsv($filePath);
        } else {
            $rows = $this->parseSpreadsheet($filePath);
        }

        if (empty($rows)) {
            throw new \InvalidArgumentException('File spreadsheet kosong atau tidak dapat dibaca.');
        }

        $headers = array_shift($rows) ?? [];
        $headers = array_map(fn ($h) => trim((string) $h), $headers);

        // Auto map headers to columns
        $mappedColumns = []; // column_key => header_index
        $unmappedHeaders = [];

        foreach ($headers as $index => $header) {
            $headerClean = mb_strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $header));
            $matchedKey = null;

            foreach ($columns as $col) {
                $keyClean = mb_strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $col->column_key));
                $labelClean = mb_strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $col->label));

                if ($headerClean === $keyClean || $headerClean === $labelClean || str_contains($labelClean, $headerClean) || str_contains($headerClean, $keyClean)) {
                    $matchedKey = $col->column_key;
                    break;
                }
            }

            if ($matchedKey !== null && ! isset($mappedColumns[$matchedKey])) {
                $mappedColumns[$matchedKey] = $index;
            } else {
                $unmappedHeaders[] = $header;
            }
        }

        // If not all columns mapped, also match by positional index
        foreach ($columns as $colIndex => $col) {
            if (! isset($mappedColumns[$col->column_key]) && isset($headers[$colIndex])) {
                $mappedColumns[$col->column_key] = $colIndex;
            }
        }

        // Build structured rows
        $parsedRows = [];
        foreach ($rows as $rawRow) {
            // Skip totally empty rows
            $hasData = false;
            foreach ($rawRow as $cell) {
                if (trim((string) $cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (! $hasData) {
                continue;
            }

            $structuredRow = [];
            foreach ($columns as $col) {
                $headerIdx = $mappedColumns[$col->column_key] ?? null;
                $structuredRow[$col->column_key] = $headerIdx !== null ? ($rawRow[$headerIdx] ?? '') : '';
            }
            $parsedRows[] = $structuredRow;
        }

        $validation = $this->datasetService->validateAndCalculateRows($template, $parsedRows);

        return [
            'headers' => $headers,
            'mapped_columns' => array_flip($mappedColumns),
            'unmapped_headers' => $unmappedHeaders,
            'raw_rows_count' => count($parsedRows),
            'preview_rows' => array_slice($validation['rows'], 0, 10),
            'validation' => $validation,
        ];
    }

    /**
     * Terapkan hasil import ke dataset aktif.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public function commitImport(Accreditation $accreditation, LkpsTemplate $template, array $rows, ?int $userId = null): LkpsDataset
    {
        return $this->datasetService->saveDataset($accreditation, $template, $rows, $userId);
    }

    /**
     * Parse file CSV.
     *
     * @return array<int, array<int, string>>
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        $rows = [];
        while (($data = fgetcsv($handle, 4096, ',')) !== false) {
            // Check if semicolon separated
            if (count($data) === 1 && str_contains((string) $data[0], ';')) {
                $data = str_getcsv((string) $data[0], ';');
            }
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Parse file XLSX / XLS menggunakan PhpSpreadsheet.
     *
     * @return array<int, array<int, string>>
     */
    private function parseSpreadsheet(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $rows = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = (string) ($cell?->getFormattedValue() ?? '');
            }
            $rows[] = $rowData;
        }

        return $rows;
    }
}
