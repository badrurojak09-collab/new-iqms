<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;
use App\Models\AssessmentElement;
use App\Models\AssessmentRubric;
use App\Models\ReadinessRun;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ReadinessScoringService
{
    public function calculate(User $actor, Accreditation $accreditation): ReadinessRun
    {
        $accreditation->loadMissing([
            'instrumentVersion.thresholds.rubric',
            'responses.instrumentNode',
            'responses.evidenceLinks.evidence',
            'readinessItems',
        ]);
        $elements = AssessmentElement::query()
            ->with(['instrumentNode', 'indicators.thresholds.rubric', 'thresholds.rubric', 'criterion'])
            ->whereHas('criterion', fn ($query) => $query->where('instrument_version_id', $accreditation->instrument_version_id))
            ->get();
        $mappings = $accreditation->instrumentVersion
            ? $accreditation->instrumentVersion->mappings()->with(['sourceIndicator.element', 'sourceIndicator.thresholds.rubric', 'targetElement'])->where('approval_status', 'approved')->get()
            : collect();
        $responses = $accreditation->responses;
        $results = $elements->map(fn (AssessmentElement $element): array => $this->evaluateElement($element, $mappings, $responses));
        if ($results->isEmpty()) {
            $results = $responses->map(fn ($response): array => $this->evaluateResponseFallback($response));
        }
        $total = $results->count();
        $ready = $results->where('status', 'ready')->count();
        $weightTotal = $results->sum(fn (array $result): float => $result['weight'] > 0 ? $result['weight'] : 1.0);
        $weightedReady = $results->sum(fn (array $result): float => $result['status'] === 'ready' ? ($result['weight'] > 0 ? $result['weight'] : 1.0) : 0.0);
        $completion = $total === 0 ? 0.0 : round(($ready / $total) * 100, 4);
        $weightedScore = $weightTotal === 0.0 ? 0.0 : round(($weightedReady / $weightTotal) * 100, 6);
        $inputHash = hash('sha256', json_encode($results->map(fn (array $result): array => [$result['item_key'], $result['status'], $result['completion'], $result['evidence'], $result['mapping_ids']])->values()->all(), JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($actor, $accreditation, $results, $total, $ready, $completion, $weightedScore, $inputHash): ReadinessRun {
            $run = ReadinessRun::query()->create([
                'accreditation_id' => $accreditation->getKey(),
                'instrument_version_id' => $accreditation->instrument_version_id,
                'created_by' => $actor->getKey(),
                'status' => 'completed',
                'engine_version' => 'readiness-mapping-v1',
                'total_items' => $total,
                'ready_items' => $ready,
                'completion_percent' => $completion,
                'weighted_score' => $weightedScore,
                'input_hash' => $inputHash,
                'summary' => ['ready' => $ready, 'total' => $total, 'mapped_elements' => $results->filter(fn (array $result): bool => ! empty($result['mapping_ids']))->count()],
                'started_at' => now(),
                'completed_at' => now(),
            ]);
            foreach ($results as $item) {
                $result = $run->results()->create([
                    'instrument_node_id' => $item['instrument_node_id'],
                    'assessment_element_id' => $item['assessment_element_id'],
                    'item_key' => $item['item_key'],
                    'status' => $item['status'],
                    'weight' => $item['weight'],
                    'completion_percent' => $item['completion'],
                    'evidence_percent' => $item['evidence'],
                    'score' => $item['score'],
                    'gap_count' => $item['status'] === 'ready' ? 0 : 1,
                    'details' => $item['details'],
                ]);
                foreach ($item['mapping_rows'] as $mappingRow) {
                    $result->mappingResults()->create(array_merge($mappingRow, ['readiness_result_id' => $result->getKey()]));
                }
                if ($item['status'] !== 'ready') {
                    $run->gaps()->create(['readiness_result_id' => $result->getKey(), 'gap_type' => $item['gap_type'], 'severity' => $item['gap_type'] === 'missing_mapping' ? 'medium' : 'high', 'item_key' => $item['item_key'], 'description' => $item['gap_reason'], 'resolution_status' => 'open']);
                }
            }

            return $run->load(['results.mappingResults.mapping', 'gaps']);
        });
    }

    /** @param Collection<int, mixed> $mappings @param Collection<int, mixed> $responses */
    private function evaluateElement(AssessmentElement $element, Collection $mappings, Collection $responses): array
    {
        $elementMappings = $mappings->where('target_element_id', $element->getKey());
        if ($elementMappings->isEmpty()) {
            return $this->baseResult($element->code, $element->instrument_node_id, $element->getKey(), (float) ($element->weight ?? 0), 'missing_mapping', 'Elemen belum memiliki mapping approved dari indikator AMI/SPMI.');
        }
        $mappingRows = [];
        foreach ($elementMappings as $mapping) {
            $indicator = $mapping->sourceIndicator;
            $response = $responses->first(fn ($candidate): bool => $candidate->response_key === $indicator?->code || (int) $candidate->instrument_node_id === (int) $indicator?->element?->instrument_node_id);
            $completion = $response && $this->hasValue($response) && in_array($response->status, ['submitted', 'accepted', 'complete', 'ready'], true) ? 100.0 : 0.0;
            $thresholds = $this->findThresholds($indicator, $element);
            $thresholdResult = $thresholds->isNotEmpty() && $response ? $this->evaluateThresholdGroup($thresholds, $response, $indicator?->direction) : null;
            if ($thresholdResult !== null) {
                $completion = $thresholdResult['passed'] ? 100.0 : 0.0;
            }
            $evidence = $response && $this->hasAcceptedEvidence($response) ? 100.0 : 0.0;
            $ready = $completion === 100.0 && $evidence === 100.0;
            $rubric = $thresholdResult ? $this->selectRubric($thresholds, $element, $thresholdResult['value']) : null;
            $mappingRows[] = ['instrument_mapping_id' => $mapping->getKey(), 'source_indicator_id' => $mapping->source_indicator_id, 'coverage_weight' => $mapping->coverage_weight, 'source_completion_percent' => $completion, 'source_evidence_percent' => $evidence, 'status' => $ready ? 'ready' : 'not_ready', 'gap_reason' => $ready ? null : ($completion < 100 ? 'Response indikator belum mencapai threshold.' : 'Evidence indikator belum accepted/verified.'), 'details' => ['response_id' => $response?->getKey(), 'threshold_ids' => $thresholds->modelKeys(), 'threshold_result' => $thresholdResult, 'rubric_id' => $rubric?->getKey(), 'rubric_label' => $rubric?->label]];
        }
        $weight = (float) ($element->weight ?? 0);
        $completion = $this->weightedAverage($mappingRows, 'source_completion_percent');
        $evidence = $this->weightedAverage($mappingRows, 'source_evidence_percent');
        $ready = $completion === 100.0 && $evidence === 100.0;

        return ['item_key' => $element->code, 'instrument_node_id' => $element->instrument_node_id, 'assessment_element_id' => $element->getKey(), 'status' => $ready ? 'ready' : 'not_ready', 'weight' => $weight, 'completion' => $completion, 'evidence' => $evidence, 'score' => $ready ? 100 : round(($completion + $evidence) / 2, 6), 'gap_type' => $ready ? null : ($evidence < 100 ? 'missing_or_unaccepted_evidence' : 'incomplete_mapped_indicator'), 'gap_reason' => $ready ? null : 'Satu atau lebih indikator mapping belum memenuhi response dan evidence valid.', 'mapping_ids' => $elementMappings->pluck('id')->values()->all(), 'mapping_rows' => $mappingRows, 'details' => ['mapping_count' => $elementMappings->count()]];
    }

    private function findThresholds($indicator, AssessmentElement $element): Collection
    {
        $thresholds = $indicator?->thresholds?->where('status', 'approved') ?? collect();

        return $thresholds->isNotEmpty() ? $thresholds->sortBy('sequence')->values() : $element->thresholds->where('status', 'approved')->sortBy('sequence')->values();
    }

    /** @return array{passed: bool, score: float, value: float|null, results: array<int, array<string, mixed>>, operator: string} */
    private function evaluateThresholdGroup(Collection $thresholds, $response, ?string $indicatorDirection): array
    {
        $value = $response->response_numeric !== null ? (float) $response->response_numeric : (is_numeric($response->response_text) ? (float) $response->response_text : null);
        $results = $thresholds->map(fn ($threshold): array => $this->evaluateThreshold($threshold, $value, $indicatorDirection))->values()->all();
        $operator = (string) ($thresholds->first()->aggregation_operator ?: 'all');
        $passedCount = collect($results)->where('passed', true)->count();
        $required = (int) ($thresholds->first()->aggregation_min_passed ?: ($operator === 'any' ? 1 : $thresholds->count()));
        $weighted = collect($results)->sum(fn (array $result): float => $result['score'] * $result['weight']) / max(0.0001, $thresholds->sum(fn ($threshold): float => (float) ($threshold->weight ?: 1)));
        $passed = match ($operator) {
            'any', 'sum' => $passedCount >= $required,
            'weighted_average' => $weighted >= (float) ($thresholds->first()->minimum_score ?? 0),
            default => $passedCount >= $required,
        };
        $score = $operator === 'weighted_average' ? round($weighted, 6) : ($passed ? (float) $thresholds->first()->pass_score : (float) $thresholds->first()->fail_score);

        return ['passed' => $passed, 'score' => $score, 'value' => $value, 'results' => $results, 'operator' => $operator];
    }

    /** @return array{passed: bool, score: float, value: float|null, weight: float} */
    private function evaluateThreshold($threshold, ?float $value, ?string $indicatorDirection): array
    {
        if ($value === null) {
            return ['passed' => false, 'score' => (float) $threshold->fail_score, 'value' => null, 'weight' => (float) ($threshold->weight ?: 1)];
        }
        $direction = $threshold->direction === 'auto' ? ($indicatorDirection ?: ($threshold->comparison === 'lte' ? 'lower_is_better' : ($threshold->comparison === 'target_match' ? 'target_match' : 'higher_is_better'))) : $threshold->direction;
        $passed = match ($direction) {
            'lower_is_better' => $threshold->target_value !== null && $value <= (float) $threshold->target_value,
            'target_match' => $threshold->target_value !== null && abs($value - (float) $threshold->target_value) < 0.000001,
            default => $threshold->comparison === 'between' ? ($threshold->min_value !== null && $threshold->max_value !== null && $value >= (float) $threshold->min_value && $value <= (float) $threshold->max_value) : ($threshold->target_value !== null && $value >= (float) $threshold->target_value),
        };
        $score = $passed ? (float) $threshold->pass_score : (float) $threshold->fail_score;
        if ($passed && $threshold->minimum_score !== null && $score < (float) $threshold->minimum_score) {
            $passed = false;
            $score = (float) $threshold->fail_score;
        }

        return ['passed' => $passed, 'score' => $score, 'value' => $value, 'weight' => (float) ($threshold->weight ?: 1)];
    }

    private function selectRubric(Collection $thresholds, AssessmentElement $element, ?float $value): ?AssessmentRubric
    {
        if ($value === null) {
            return null;
        }
        $rubrics = $thresholds->map(fn ($threshold) => $threshold->rubric)->filter(fn ($rubric): bool => $rubric->status === 'approved');
        if ($rubrics->isEmpty()) {
            $rubrics = AssessmentRubric::query()->where('instrument_version_id', $element->criterion?->instrument_version_id)->where(function ($query) use ($element): void {
                $query->where('instrument_node_id', $element->instrument_node_id)->orWhereNull('instrument_node_id');
            })->get();
        }

        return $rubrics->first(fn ($rubric): bool => $rubric->status === 'approved' && ($rubric->min_score === null || $value >= (float) $rubric->min_score) && ($rubric->max_score === null || $value <= (float) $rubric->max_score));
    }

    private function evaluateResponseFallback($response): array
    {
        $complete = $this->hasValue($response) && in_array($response->status, ['submitted', 'accepted', 'complete', 'ready'], true);
        $evidence = $this->hasAcceptedEvidence($response);

        return ['item_key' => $response->response_key, 'instrument_node_id' => $response->instrument_node_id, 'assessment_element_id' => null, 'status' => $complete && $evidence ? 'ready' : 'not_ready', 'weight' => (float) ($response->instrumentNode?->weight ?? 0), 'completion' => $complete ? 100.0 : 0.0, 'evidence' => $evidence ? 100.0 : 0.0, 'score' => $complete && $evidence ? 100 : 0, 'gap_type' => $evidence ? 'incomplete_response' : 'missing_or_unaccepted_evidence', 'gap_reason' => $complete && $evidence ? null : 'Response atau evidence belum memenuhi syarat.', 'mapping_ids' => [], 'mapping_rows' => [], 'details' => ['response_id' => $response->getKey()]];
    }

    private function baseResult(string $key, ?int $nodeId, ?int $elementId, float $weight, string $gapType, string $reason): array
    {
        return ['item_key' => $key, 'instrument_node_id' => $nodeId, 'assessment_element_id' => $elementId, 'status' => 'not_ready', 'weight' => $weight, 'completion' => 0.0, 'evidence' => 0.0, 'score' => 0.0, 'gap_type' => $gapType, 'gap_reason' => $reason, 'mapping_ids' => [], 'mapping_rows' => [], 'details' => []];
    }

    private function hasValue($response): bool
    {
        return filled($response->response_text) || $response->response_numeric !== null || filled($response->response_json);
    }

    private function hasAcceptedEvidence($response): bool
    {
        return $response->evidenceLinks->contains(fn ($link): bool => $link->evidence?->status === 'verified');
    }

    private function weightedAverage(array $rows, string $field): float
    {
        $total = array_sum(array_map(fn (array $row): float => (float) ($row['coverage_weight'] ?? 1), $rows));

        return $total === 0.0 ? 0.0 : round(array_sum(array_map(fn (array $row): float => ((float) $row[$field]) * (float) ($row['coverage_weight'] ?? 1), $rows)) / $total, 4);
    }
}
