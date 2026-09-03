<?php

declare(strict_types=1);

namespace App\Domain\Lkps;

use App\Models\Accreditation;
use App\Models\LkpsDataset;
use App\Models\LkpsTemplate;
use App\Models\LkpsTemplateColumn;
use Illuminate\Database\Eloquent\Collection;

final class LkpsDatasetService
{
    /**
     * Dapatkan atau inisialisasi dataset LKPS untuk kegiatan dan template tertentu.
     */
    public function getOrCreateDataset(Accreditation $accreditation, LkpsTemplate $template): LkpsDataset
    {
        /** @var LkpsDataset $dataset */
        $dataset = LkpsDataset::query()->firstOrCreate(
            [
                'accreditation_id' => $accreditation->getKey(),
                'lkps_template_id' => $template->getKey(),
            ],
            [
                'status' => LkpsDataset::STATUS_DRAFT,
                'rows_data' => $this->generateInitialRows($template),
                'summary_metrics' => [],
                'validation_errors' => [],
            ]
        );

        if (empty($dataset->rows_data)) {
            $dataset->rows_data = $this->generateInitialRows($template);
            $dataset->save();
        }

        return $dataset;
    }

    /**
     * Simpan baris data ke dataset dan validasi otomatis serta evaluasi summary/formula.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public function saveDataset(Accreditation $accreditation, LkpsTemplate $template, array $rows, ?int $userId = null): LkpsDataset
    {
        $dataset = $this->getOrCreateDataset($accreditation, $template);

        if ($dataset->isLocked()) {
            throw new \DomainException('Dataset LKPS ini telah dikunci dan tidak dapat diubah.');
        }

        $validated = $this->validateAndCalculateRows($template, $rows);

        $dataset->rows_data = $validated['rows'];
        $dataset->summary_metrics = $validated['summary'];
        $dataset->validation_errors = $validated['errors'];
        $dataset->status = empty($validated['errors']) && count($validated['rows']) > 0
            ? LkpsDataset::STATUS_APPROVED
            : LkpsDataset::STATUS_DRAFT;
        $dataset->last_edited_by = $userId;
        $dataset->save();

        return $dataset;
    }

    /**
     * Validasi dan kalkulasi formula setiap baris data.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, array<string, string>>, summary: array<string, mixed>}
     */
    public function validateAndCalculateRows(LkpsTemplate $template, array $rows): array
    {
        $template->loadMissing('columns');
        /** @var Collection<int, LkpsTemplateColumn> $columns */
        $columns = $template->columns->sortBy('sort_order');

        $cleanRows = [];
        $errors = [];
        $columnTotals = [];

        foreach ($columns as $col) {
            if (in_array($col->data_type, ['integer', 'decimal', 'number'], true)) {
                $columnTotals[$col->column_key] = 0.0;
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $cleanRow = [];
            $rowErrors = [];

            foreach ($columns as $col) {
                $key = $col->column_key;
                $val = $row[$key] ?? null;

                // Handle formula if defined
                if (! empty($col->formula) && is_array($col->formula)) {
                    $val = $this->evaluateFormula($col->formula, $row);
                }

                // Check required
                if ($col->is_required && ($val === null || $val === '')) {
                    $rowErrors[$key] = "Kolom '{$col->label}' wajib diisi.";
                } elseif ($val !== null && $val !== '') {
                    // Type checking & conversions
                    if ($col->data_type === 'integer') {
                        if (! is_numeric($val)) {
                            $rowErrors[$key] = "Kolom '{$col->label}' harus berupa angka bulat.";
                        } else {
                            $val = (int) $val;
                            if ($col->min_value !== null && $val < (int) $col->min_value) {
                                $rowErrors[$key] = "Nilai minimal adalah {$col->min_value}.";
                            }
                            if ($col->max_value !== null && $val > (int) $col->max_value) {
                                $rowErrors[$key] = "Nilai maksimal adalah {$col->max_value}.";
                            }
                            $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $val;
                        }
                    } elseif (in_array($col->data_type, ['decimal', 'number', 'percent', 'ratio'], true)) {
                        if (! is_numeric($val)) {
                            $rowErrors[$key] = "Kolom '{$col->label}' harus berupa angka numerik.";
                        } else {
                            $val = (float) $val;
                            if ($col->min_value !== null && $val < (float) $col->min_value) {
                                $rowErrors[$key] = "Nilai minimal adalah {$col->min_value}.";
                            }
                            if ($col->max_value !== null && $val > (float) $col->max_value) {
                                $rowErrors[$key] = "Nilai maksimal adalah {$col->max_value}.";
                            }
                            if ($col->decimal_scale !== null) {
                                $val = round($val, (int) $col->decimal_scale);
                            }
                            $columnTotals[$key] = ($columnTotals[$key] ?? 0) + $val;
                        }
                    } elseif ($col->data_type === 'boolean') {
                        $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                    } elseif (! empty($col->allowed_values) && is_array($col->allowed_values)) {
                        if (! in_array($val, $col->allowed_values, true)) {
                            $rowErrors[$key] = "Nilai tidak valid. Pilihan yang diperbolehkan: " . implode(', ', $col->allowed_values);
                        }
                    }
                }

                $cleanRow[$key] = $val;
            }

            if (! empty($rowErrors)) {
                $errors[$rowIndex] = $rowErrors;
            }

            $cleanRows[] = $cleanRow;
        }

        $summary = [
            'total_rows' => count($cleanRows),
            'valid_rows' => count($cleanRows) - count($errors),
            'has_errors' => ! empty($errors),
            'column_totals' => $columnTotals,
            'calculated_at' => now()->toIso8601String(),
        ];

        return [
            'rows' => $cleanRows,
            'errors' => $errors,
            'summary' => $summary,
        ];
    }

    /**
     * Hitung persentase keterisian dan kesiapan seluruh tabel LKPS pada satu akreditasi.
     */
    public function calculateOverallLkpsProgress(Accreditation $accreditation): float
    {
        $versionId = $accreditation->instrument_version_id;
        if (! $versionId) {
            return 0.0;
        }

        $templates = LkpsTemplate::query()
            ->where('instrument_version_id', $versionId)
            ->with(['columns', 'datasets' => fn ($q) => $q->where('accreditation_id', $accreditation->getKey())])
            ->get();

        if ($templates->isEmpty()) {
            return 100.0;
        }

        $completedCount = 0;
        foreach ($templates as $template) {
            $dataset = $template->datasets->first();
            if ($dataset && ! empty($dataset->rows_data) && empty($dataset->validation_errors)) {
                $completedCount++;
            }
        }

        return round(($completedCount / $templates->count()) * 100, 2);
    }

    /**
     * Generate baris awal sesuai row_definition template bila ada (misal: daftar periode tahun).
     *
     * @return array<int, array<string, mixed>>
     */
    private function generateInitialRows(LkpsTemplate $template): array
    {
        $rowDef = $template->row_definition;
        $template->loadMissing('columns');
        $columns = $template->columns;

        // Default empty row
        $defaultRow = [];
        foreach ($columns as $col) {
            $defaultRow[$col->column_key] = '';
        }

        if (isset($rowDef['periods']) && is_array($rowDef['periods'])) {
            $rows = [];
            foreach ($rowDef['periods'] as $period) {
                $row = $defaultRow;
                if (isset($row['periode']) || isset($row['tahun']) || isset($row['tahun_akademik'])) {
                    $periodKey = isset($row['periode']) ? 'periode' : (isset($row['tahun']) ? 'tahun' : 'tahun_akademik');
                    $row[$periodKey] = (string) $period;
                }
                $rows[] = $row;
            }
            return $rows;
        }

        return [$defaultRow];
    }

    /**
     * Evaluasi formula sederhana (penjumlahan/pembagian antar kolom).
     *
     * @param array<string, mixed> $formula
     * @param array<string, mixed> $row
     */
    private function evaluateFormula(array $formula, array $row): mixed
    {
        $op = $formula['operator'] ?? null;
        $fields = $formula['fields'] ?? [];

        if ($op === 'sum' && is_array($fields)) {
            $sum = 0.0;
            foreach ($fields as $field) {
                $sum += (float) ($row[$field] ?? 0);
            }
            return $sum;
        }

        if ($op === 'ratio' && count($fields) >= 2) {
            $a = (float) ($row[$fields[0]] ?? 0);
            $b = (float) ($row[$fields[1]] ?? 0);
            return $b > 0 ? round($a / $b, 2) : 0.0;
        }

        if ($op === 'percentage' && count($fields) >= 2) {
            $a = (float) ($row[$fields[0]] ?? 0);
            $b = (float) ($row[$fields[1]] ?? 0);
            return $b > 0 ? round(($a / $b) * 100, 2) : 0.0;
        }

        return null;
    }
}
