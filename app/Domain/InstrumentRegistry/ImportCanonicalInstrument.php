<?php

declare(strict_types=1);

namespace App\Domain\InstrumentRegistry;

use App\Models\AssessmentCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentIndicator;
use App\Models\AssessmentRubric;
use App\Models\AssessmentScale;
use App\Models\AssessmentScaleOption;
use App\Models\AssessmentThreshold;
use App\Models\InstrumentFamily;
use App\Models\InstrumentImportBatch;
use App\Models\InstrumentScoringRule;
use App\Models\InstrumentVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

final class ImportCanonicalInstrument
{
    private const ENTITY_TYPES = [
        'node', 'criterion', 'element', 'indicator', 'scale', 'scale_option',
        'rubric', 'threshold', 'qualification_rule',
    ];

    /** @return array{headers: array<int, string>, rows: array<int, array<string, mixed>>, errors: array<int, string>} */
    public function preview(string $path): array
    {
        if (! is_file($path)) {
            throw ValidationException::withMessages(['file' => 'File import tidak ditemukan.']);
        }

        $rawRows = $this->readRows($path);
        $headerRow = array_shift($rawRows) ?: [];
        $headers = array_values(array_filter(array_map(fn ($value): string => $this->normalizeHeader((string) $value), $headerRow)));
        if ($headers === []) {
            throw ValidationException::withMessages(['file' => 'Header file import kosong.']);
        }

        $required = ['entity_type', 'code', 'title'];
        $missing = array_values(array_diff($required, $headers));
        if ($missing !== []) {
            throw ValidationException::withMessages(['file' => 'Kolom wajib tidak ada: '.implode(', ', $missing)]);
        }

        $rows = [];
        $errors = [];
        $seenCodes = [];
        foreach ($rawRows as $index => $raw) {
            $values = array_values($raw);
            $row = [];
            foreach ($headers as $position => $header) {
                $row[$header] = isset($values[$position]) ? trim((string) $values[$position]) : null;
            }
            if ($this->isBlankRow($row)) {
                continue;
            }
            $rowNumber = $index + 2;
            $rowErrors = $this->validateRow($row);
            $identity = strtolower((string) ($row['entity_type'] ?? '')).'::'.strtolower((string) ($row['code'] ?? ''));
            if (($row['code'] ?? '') !== '' && isset($seenCodes[$identity])) {
                $rowErrors[] = 'Kombinasi entity_type dan code duplikat dengan baris '.$seenCodes[$identity].'.';
            }
            if (($row['code'] ?? '') !== '') {
                $seenCodes[$identity] = $rowNumber;
            }
            if ($rowErrors !== []) {
                $errors[$rowNumber] = implode(' ', $rowErrors);
                continue;
            }
            $row['_row_number'] = $rowNumber;
            $rows[$rowNumber] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows, 'errors' => $errors];
    }

    public function commit(User $actor, InstrumentFamily $family, string $path, string $originalName, string $versionLabel): InstrumentVersion
    {
        $preview = $this->preview($path);
        if ($preview['errors'] !== []) {
            throw ValidationException::withMessages(['file' => 'Import memiliki error pada baris: '.implode(', ', array_keys($preview['errors']))]);
        }

        return DB::transaction(function () use ($actor, $family, $path, $originalName, $versionLabel, $preview): InstrumentVersion {
            $sourceHash = hash_file('sha256', $path);
            if (! is_string($sourceHash)) {
                throw new RuntimeException('Source hash gagal dihitung.');
            }

            $batch = InstrumentImportBatch::query()->create([
                'instrument_family_id' => $family->getKey(),
                'created_by' => $actor->getKey(),
                'original_name' => $originalName,
                'format' => strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'xlsx',
                'source_hash' => $sourceHash,
                'status' => 'committed',
                'total_rows' => count($preview['rows']),
                'valid_rows' => count($preview['rows']),
                'error_rows' => 0,
                'summary' => ['headers' => $preview['headers'], 'format_version' => 'canonical-v2'],
                'committed_at' => now(),
            ]);
            $version = $family->versions()->create([
                'version_label' => $versionLabel,
                'status' => 'draft',
                'source_reference' => $originalName,
                'changelog' => ['import_batch_id' => $batch->getKey(), 'source_hash' => $sourceHash, 'format_version' => 'canonical-v2'],
            ]);

            $rows = array_values($preview['rows']);
            $nodes = $this->importNodes($version, $batch, $rows);
            $criteria = $this->importCriteria($version, $batch, $rows, $nodes);
            $elements = $this->importElements($version, $batch, $rows, $nodes, $criteria);
            $indicators = $this->importIndicators($version, $batch, $rows, $elements);
            $scales = $this->importScales($version, $batch, $rows);
            $options = $this->importScaleOptions($version, $batch, $rows, $scales);
            $rubrics = $this->importRubrics($version, $batch, $rows, $nodes, $options);
            $this->importThresholds($version, $batch, $rows, $elements, $indicators, $scales, $rubrics);
            $qualificationRules = $this->importQualificationRules($version, $batch, $rows);

            if ($qualificationRules !== []) {
                $changelog = $version->changelog ?? [];
                $changelog['qualification_rules'] = $qualificationRules;
                $version->update(['changelog' => $changelog]);
            }

            return $version->load('nodes');
        });
    }

    /** @return array<int, array<int, mixed>> */
    private function readRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contents = (string) file_get_contents($path);
        $looksLikeJson = in_array($extension, ['json', 'jsonl'], true) || in_array(substr(ltrim($contents), 0, 1), ['{', '['], true);
        if ($looksLikeJson) {
            $decoded = json_decode($contents, true);
            if (! is_array($decoded)) {
                throw ValidationException::withMessages(['file' => 'JSON import tidak valid.']);
            }
            $rows = array_is_list($decoded) ? $decoded : ($decoded['rows'] ?? []);
            if (! is_array($rows)) {
                throw ValidationException::withMessages(['file' => 'JSON harus berupa array baris atau objek dengan properti rows.']);
            }
            $headers = [];
            foreach ($rows as $row) {
                if (is_array($row)) {
                    $headers = array_values(array_unique([...$headers, ...array_keys($row)]));
                }
            }
            $normalizedRows = array_map(function ($row) use ($headers): array {
                $row = is_array($row) ? $row : [];
                return array_map(fn (string $header) => is_scalar($row[$header] ?? null) || ($row[$header] ?? null) === null ? ($row[$header] ?? null) : json_encode($row[$header]), $headers);
            }, $rows);
            return [$headers, ...$normalizedRows];
        }

        $sheet = IOFactory::load($path)->getActiveSheet();
        return $sheet->toArray(null, true, true, true);
    }

    /** @param array<string, mixed> $row */
    private function validateRow(array $row): array
    {
        $errors = [];
        $type = strtolower((string) ($row['entity_type'] ?? ''));
        if (! in_array($type, self::ENTITY_TYPES, true)) {
            $errors[] = 'entity_type tidak valid; gunakan node, criterion, element, indicator, scale, scale_option, rubric, threshold, atau qualification_rule.';
        }
        if (($row['code'] ?? '') === '') {
            $errors[] = 'code wajib diisi.';
        }
        $titleMissing = ($row['title'] ?? '') === '';
        if ($type !== 'qualification_rule' && $type !== 'scale_option' && $type !== 'rubric' && $titleMissing) {
            $errors[] = 'title wajib diisi untuk entity tersebut.';
        }
        if ($type === 'scale_option' && ($row['title'] ?? $row['label'] ?? '') === '') {
            $errors[] = 'scale_option wajib memiliki title atau label.';
        }
        if ($type === 'criterion' && ($row['node_code'] ?? '') === '') {
            $errors[] = 'criterion wajib memiliki node_code.';
        }
        if ($type === 'element' && (($row['criterion_code'] ?? '') === '' || ($row['node_code'] ?? '') === '')) {
            $errors[] = 'element wajib memiliki criterion_code dan node_code.';
        }
        if ($type === 'indicator' && ($row['element_code'] ?? '') === '') {
            $errors[] = 'indicator wajib memiliki element_code.';
        }
        if ($type === 'scale_option' && ($row['scale_code'] ?? '') === '') {
            $errors[] = 'scale_option wajib memiliki scale_code.';
        }
        if ($type === 'rubric' && ($row['label'] ?? $row['title'] ?? '') === '') {
            $errors[] = 'rubric wajib memiliki label atau title.';
        }
        if ($type === 'threshold' && ($row['indicator_code'] ?? $row['element_code'] ?? '') === '') {
            $errors[] = 'threshold wajib memiliki indicator_code atau element_code.';
        }
        if ($type === 'qualification_rule' && ($row['rule_type'] ?? '') === '') {
            $errors[] = 'qualification_rule wajib memiliki rule_type.';
        }
        return $errors;
    }

    /** @param array<string, mixed> $row */
    private function isBlankRow(array $row): bool
    {
        return count(array_filter($row, fn ($value): bool => $value !== null && trim((string) $value) !== '')) === 0;
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function importNodes(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows): array
    {
        $nodes = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'node') continue;
            $parent = $nodes[$row['parent_code'] ?? ''] ?? null;
            $nodes[$row['code']] = $version->nodes()->create([
                'parent_id' => $parent?->getKey(), 'node_type' => $row['node_type'] ?? 'element', 'code' => $row['code'],
                'title' => $row['title'], 'requirement' => $row['requirement'] ?? null, 'guidance' => $row['guidance'] ?? null,
                'weight' => $this->nullableNumber($row['weight'] ?? null), 'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_required' => $this->toBool($row['is_required'] ?? false), 'metadata' => $this->jsonValue($row['metadata'] ?? null),
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $nodes;
    }

    private function importCriteria(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $nodes): array
    {
        $criteria = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'criterion') continue;
            $node = $nodes[$row['node_code']] ?? null;
            if (! $node) throw new RuntimeException("Node {$row['node_code']} tidak ditemukan untuk criterion {$row['code']}.");
            $criteria[$row['code']] = AssessmentCriterion::query()->create([
                'instrument_version_id' => $version->getKey(), 'instrument_node_id' => $node->getKey(), 'code' => $row['code'],
                'name' => $row['title'], 'weight' => $this->nullableNumber($row['weight'] ?? null),
                'minimum_score' => $this->nullableNumber($row['minimum_score'] ?? null), 'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_required' => $this->toBool($row['is_required'] ?? true),
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $criteria;
    }

    private function importElements(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $nodes, array $criteria): array
    {
        $elements = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'element') continue;
            $criterion = $criteria[$row['criterion_code']] ?? null;
            $node = $nodes[$row['node_code']] ?? null;
            if (! $criterion || ! $node) throw new RuntimeException("Relasi element {$row['code']} tidak ditemukan.");
            $elements[$row['code']] = AssessmentElement::query()->create([
                'assessment_criterion_id' => $criterion->getKey(), 'instrument_node_id' => $node->getKey(), 'code' => $row['code'],
                'title' => $row['title'], 'element_type' => $row['element_type'] ?? 'mixed', 'weight' => $this->nullableNumber($row['weight'] ?? null),
                'is_required' => $this->toBool($row['is_required'] ?? true), 'sort_order' => (int) ($row['sort_order'] ?? 0),
                'metadata' => $this->jsonValue($row['metadata'] ?? null),
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $elements;
    }

    private function importIndicators(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $elements): array
    {
        $indicators = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'indicator') continue;
            $element = $elements[$row['element_code']] ?? null;
            if (! $element) throw new RuntimeException("Element {$row['element_code']} tidak ditemukan.");
            $indicators[$row['code']] = AssessmentIndicator::query()->create([
                'assessment_element_id' => $element->getKey(), 'code' => $row['code'], 'name' => $row['title'], 'unit' => $row['unit'] ?? null,
                'direction' => $row['direction'] ?? 'higher_is_better', 'data_type' => $row['data_type'] ?? 'decimal',
                'target_definition' => $this->jsonValue($row['target_definition'] ?? null), 'is_required' => $this->toBool($row['is_required'] ?? false),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $indicators;
    }

    private function importScales(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows): array
    {
        $scales = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'scale') continue;
            $scales[$row['code']] = $version->assessmentScales()->create([
                'code' => $row['code'], 'name' => $row['title'], 'scale_type' => $row['scale_type'] ?? 'numeric',
                'min_value' => $this->nullableNumber($row['min_value'] ?? null), 'max_value' => $this->nullableNumber($row['max_value'] ?? null),
                'precision' => ($row['precision'] ?? '') !== '' ? (int) $row['precision'] : null,
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $scales;
    }

    private function importScaleOptions(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $scales): array
    {
        $options = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'scale_option') continue;
            $scale = $scales[$row['scale_code']] ?? null;
            if (! $scale) throw new RuntimeException("Scale {$row['scale_code']} tidak ditemukan.");
            $options[$row['code']] = $scale->options()->create([
                'code' => $row['code'], 'label' => $row['label'] ?? $row['title'], 'numeric_value' => $this->nullableNumber($row['numeric_value'] ?? null),
                'sort_order' => (int) ($row['sort_order'] ?? 0), 'metadata' => $this->jsonValue($row['metadata'] ?? null),
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $options;
    }

    private function importRubrics(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $nodes, array $options): array
    {
        $rubrics = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'rubric') continue;
            $rubrics[$row['code']] = AssessmentRubric::query()->create([
                'instrument_version_id' => $version->getKey(), 'instrument_node_id' => isset($nodes[$row['node_code'] ?? '']) ? $nodes[$row['node_code']]->getKey() : null,
                'assessment_scale_option_id' => isset($options[$row['scale_option_code'] ?? '']) ? $options[$row['scale_option_code']]->getKey() : null,
                'min_score' => $this->nullableNumber($row['min_score'] ?? null), 'max_score' => $this->nullableNumber($row['max_score'] ?? null),
                'label' => $row['label'] ?? $row['title'], 'description' => $row['description'] ?? $row['guidance'] ?? '',
                'evidence_expectation' => $row['evidence_expectation'] ?? null,
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
        return $rubrics;
    }

    private function importThresholds(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows, array $elements, array $indicators, array $scales, array $rubrics): void
    {
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'threshold') continue;
            AssessmentThreshold::query()->create([
                'instrument_version_id' => $version->getKey(), 'assessment_element_id' => isset($elements[$row['element_code'] ?? '']) ? $elements[$row['element_code']]->getKey() : null,
                'assessment_indicator_id' => isset($indicators[$row['indicator_code'] ?? '']) ? $indicators[$row['indicator_code']]->getKey() : null,
                'assessment_scale_id' => isset($scales[$row['scale_code'] ?? '']) ? $scales[$row['scale_code']]->getKey() : null,
                'assessment_rubric_id' => isset($rubrics[$row['rubric_code'] ?? '']) ? $rubrics[$row['rubric_code']]->getKey() : null,
                'code' => $row['code'], 'name' => $row['title'], 'comparison' => $row['comparison'] ?? 'gte',
                'target_value' => $this->nullableNumber($row['target_value'] ?? null), 'min_value' => $this->nullableNumber($row['min_value'] ?? null),
                'max_value' => $this->nullableNumber($row['max_value'] ?? null), 'pass_score' => $this->nullableNumber($row['pass_score'] ?? null) ?? 100,
                'fail_score' => $this->nullableNumber($row['fail_score'] ?? null) ?? 0, 'minimum_score' => $this->nullableNumber($row['minimum_score'] ?? null),
                'weight' => $this->nullableNumber($row['weight'] ?? null) ?? 1, 'status' => $row['status'] ?? 'draft',
                'config' => $this->jsonValue($row['config'] ?? null), 'source_reference' => $row['source_reference'] ?? null,
                'direction' => $row['direction'] ?? null, 'aggregation_key' => $row['aggregation_key'] ?? null,
                'aggregation_operator' => $row['aggregation_operator'] ?? 'all', 'aggregation_min_passed' => ($row['aggregation_min_passed'] ?? '') !== '' ? (int) $row['aggregation_min_passed'] : null,
                'sequence' => ($row['sequence'] ?? '') !== '' ? (int) $row['sequence'] : 0,
            ]);
            $this->recordRow($batch, $row, 'valid');
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function importQualificationRules(InstrumentVersion $version, InstrumentImportBatch $batch, array $rows): array
    {
        $rules = [];
        foreach ($rows as $row) {
            if (($row['entity_type'] ?? '') !== 'qualification_rule') continue;
            $expression = $this->jsonValue($row['expression'] ?? null) ?? [];
            $parameters = $this->jsonValue($row['parameters'] ?? null) ?? [];
            InstrumentScoringRule::query()->create([
                'instrument_version_id' => $version->getKey(), 'code' => $row['code'], 'rule_type' => $row['rule_type'],
                'expression' => $expression, 'parameters' => $parameters,
            ]);
            $rules[] = ['code' => $row['code'], 'rule_type' => $row['rule_type'], 'expression' => $expression, 'parameters' => $parameters, 'source_reference' => $row['source_reference'] ?? null];
            $this->recordRow($batch, $row, 'valid');
        }
        return $rules;
    }

    private function recordRow(InstrumentImportBatch $batch, array $row, string $status): void
    {
        $batch->rows()->create(['row_number' => (int) ($row['_row_number'] ?? 0), 'entity_type' => $row['entity_type'], 'entity_code' => $row['code'], 'payload' => $row, 'status' => $status]);
    }

    private function normalizeHeader(string $header): string
    {
        return strtolower(trim(str_replace([' ', '-'], '_', $header)));
    }

    private function nullableNumber(mixed $value): mixed
    {
        return ($value === null || trim((string) $value) === '') ? null : $value;
    }

    private function toBool(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y'], true);
    }

    private function jsonValue(mixed $value): ?array
    {
        if ($value === null || trim((string) $value) === '') return null;
        if (is_array($value)) return $value;
        $decoded = json_decode((string) $value, true);
        return is_array($decoded) ? $decoded : ['value' => $value];
    }
}
