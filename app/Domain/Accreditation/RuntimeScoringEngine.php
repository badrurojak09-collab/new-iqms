<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;
use App\Models\AccreditationResponse;
use App\Models\AccreditationScoreSnapshot;
use App\Models\AssessmentThreshold;
use App\Models\EvidenceLink;
use App\Models\InstrumentScoringRule;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RuntimeScoringEngine
{
    /** @return array{instrument_version_id:int, score:float, status:string, qualification:array<string, mixed>, rules:array<int, array<string, mixed>>} */
    public function score(Accreditation $accreditation, ?string $ruleCode = null): array
    {
        $accreditation->loadMissing(['instrumentVersion', 'responses']);
        $versionId = (int) $accreditation->instrument_version_id;

        $rules = InstrumentScoringRule::query()
            ->where('instrument_version_id', $versionId)
            ->when($ruleCode !== null, fn ($query) => $query->where('code', $ruleCode))
            ->orderBy('code')
            ->get();

        $blockedResponseKeys = EvidenceLink::query()->where('linkable_type', AccreditationResponse::class)->whereIn('linkable_id', $accreditation->responses->modelKeys())->where('is_required', true)->whereHas('evidence', fn ($query) => $query->where('status', '!=', 'verified'))->with('linkable')->get()->pluck('linkable.response_key')->filter()->all();
        $values = $accreditation->responses->mapWithKeys(function ($response) use ($blockedResponseKeys): array {
            if (in_array($response->response_key, $blockedResponseKeys, true)) {
                return [$response->response_key => null];
            }
            $value = $response->response_numeric;
            if ($value === null && is_numeric($response->response_text)) {
                $value = (float) $response->response_text;
            }
            if ($value === null && is_array($response->response_json)) {
                $value = $response->response_json['value'] ?? null;
            }

            return [$response->response_key => is_numeric($value) ? (float) $value : $value];
        })->all();

        $scoringRules = $rules->reject(fn (InstrumentScoringRule $rule): bool => $rule->rule_type === 'status_qualification');
        $qualificationRules = $rules->filter(fn (InstrumentScoringRule $rule): bool => $rule->rule_type === 'status_qualification');
        $evaluated = $scoringRules->map(fn (InstrumentScoringRule $rule): array => $this->evaluate($rule, $values))->values()->all();
        $canonicalThresholds = AssessmentThreshold::query()->with(['element', 'indicator', 'rubric', 'scale'])->where('instrument_version_id', $versionId)->where('status', 'approved')->get();
        foreach ($canonicalThresholds as $threshold) {
            $field = $threshold->indicator?->code ?? $threshold->element?->code ?? $threshold->code;
            $evaluated[] = $this->evaluateCanonicalThreshold($threshold, $field, $values[$field] ?? null);
        }
        $scores = collect($evaluated)->pluck('score')->filter(fn ($score) => $score !== null)->map(fn ($score) => (float) $score);
        $score = $scores->isEmpty() ? 0.0 : round((float) $scores->avg(), 4);
        $qualification = $this->evaluateQualifications($qualificationRules->values()->all(), $score, $evaluated);
        $evaluated[] = ['rule_type' => 'qualification_summary', 'status' => $qualification['status'], 'passed' => $qualification['passed'], 'validity_years' => $qualification['validity_years'], 'failed_rules' => $qualification['failed_rules']];

        return [
            'instrument_version_id' => $versionId,
            'score' => $score,
            'status' => $qualification['status'],
            'qualification' => $qualification,
            'rules' => $evaluated,
        ];
    }

    public function scoreAndPersist(Accreditation $accreditation, ?int $userId = null): AccreditationScoreSnapshot
    {
        return DB::transaction(function () use ($accreditation, $userId): AccreditationScoreSnapshot {
            $result = $this->score($accreditation);
            $inputSnapshot = $accreditation->responses->map(fn ($response): array => [
                'response_key' => $response->response_key,
                'response_type' => $response->response_type,
                'response_text' => $response->response_text,
                'response_numeric' => $response->response_numeric,
                'response_json' => $response->response_json,
                'status' => $response->status,
            ])->values()->all();
            $canonical = json_encode(['accreditation_id' => $accreditation->getKey(), 'instrument_version_id' => $result['instrument_version_id'], 'score' => $result['score'], 'rules' => $result['rules'], 'inputs' => $inputSnapshot], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            $hash = hash('sha256', $canonical);

            $existing = AccreditationScoreSnapshot::query()
                ->where('snapshot_hash', $hash)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            return AccreditationScoreSnapshot::create([
                'accreditation_id' => $accreditation->getKey(),
                'instrument_version_id' => $result['instrument_version_id'],
                'calculated_by' => $userId,
                'score' => $result['score'],
                'status' => $result['status'],
                'snapshot_hash' => $hash,
                'rule_results' => $result['rules'],
                'input_snapshot' => $inputSnapshot,
                'calculated_at' => now(),
            ]);
        });
    }

    /** @param array<string, mixed> $values @return array<string, mixed> */
    private function evaluate(InstrumentScoringRule $rule, array $values): array
    {
        $expression = is_array($rule->expression) ? $rule->expression : [];
        $score = match ($rule->rule_type) {
            'weighted_sum' => $this->weightedSum($expression, $values),
            'threshold' => $this->threshold($expression, $values),
            'mapping' => $this->mapping($expression, $values),
            'formula' => $this->formula($expression, $values),
            default => throw new InvalidArgumentException("Unsupported scoring rule type [{$rule->rule_type}]."),
        };

        return ['code' => $rule->code, 'rule_type' => $rule->rule_type, 'score' => $score, 'expression' => $expression, 'instrument_version_id' => $rule->instrument_version_id];
    }

    private function evaluateCanonicalThreshold(AssessmentThreshold $threshold, string $field, mixed $value): array
    {
        $numeric = is_numeric($value) ? (float) $value : null;
        $passed = $numeric !== null && match ($threshold->comparison) {
            'lte' => $threshold->target_value !== null && $numeric <= (float) $threshold->target_value,
            'eq', 'target_match' => $threshold->target_value !== null && abs($numeric - (float) $threshold->target_value) < 0.000001,
            'between' => $threshold->min_value !== null && $threshold->max_value !== null && $numeric >= (float) $threshold->min_value && $numeric <= (float) $threshold->max_value,
            default => $threshold->target_value !== null && $numeric >= (float) $threshold->target_value,
        };
        $score = $passed ? (float) $threshold->pass_score : (float) $threshold->fail_score;
        if ($passed && $threshold->minimum_score !== null && $score < (float) $threshold->minimum_score) {
            $passed = false;
            $score = (float) $threshold->fail_score;
        }

        return ['code' => $threshold->code, 'rule_type' => 'canonical_threshold', 'field' => $field, 'score' => $score, 'passed' => $passed, 'value' => $numeric, 'target_value' => $threshold->target_value, 'comparison' => $threshold->comparison, 'rubric_id' => $threshold->assessment_rubric_id, 'scale_id' => $threshold->assessment_scale_id, 'weight' => (float) $threshold->weight, 'aggregation_key' => $threshold->aggregation_key, 'instrument_version_id' => $threshold->instrument_version_id];
    }

    /** @param array<int, InstrumentScoringRule> $rules @param array<int, array<string, mixed>> $evaluated @return array<string, mixed> */
    private function evaluateQualifications(array $rules, float $score, array $evaluated): array
    {
        usort($rules, function (InstrumentScoringRule $a, InstrumentScoringRule $b): int {
            $aParameters = is_array($a->parameters) ? $a->parameters : [];
            $bParameters = is_array($b->parameters) ? $b->parameters : [];
            return (int) ($bParameters['validity_years'] ?? 0) <=> (int) ($aParameters['validity_years'] ?? 0);
        });
        $groupScores = [];
        $weightedTotal = 0.0;
        $weightTotal = 0.0;
        foreach ($evaluated as $item) {
            if (! is_numeric($item['score'] ?? null)) continue;
            $itemScore = (float) $item['score'];
            $weight = is_numeric($item['weight'] ?? null) ? (float) $item['weight'] : 0.0;
            if ($weight > 0) { $weightedTotal += $itemScore * $weight; $weightTotal += $weight; }
            $group = $item['aggregation_key'] ?? null;
            if ($group !== null) $groupScores[(string) $group][] = $itemScore;
        }
        $scoreScaleMax = 4.0;
        foreach ($rules as $rule) {
            $parameters = is_array($rule->parameters) ? $rule->parameters : [];
            if (is_numeric($parameters['score_scale_max'] ?? null) && (float) $parameters['score_scale_max'] > 0) {
                $scoreScaleMax = (float) $parameters['score_scale_max'];
                break;
            }
        }
        $context = ['final_score' => $weightTotal > 0 ? round($weightedTotal / $scoreScaleMax, 4) : $score, 'average_score' => $score, 'score' => $score];
        foreach ($groupScores as $group => $groupValues) {
            $context[$group.'.item_min'] = min($groupValues);
            $context[$group.'.item_average'] = round((float) (array_sum($groupValues) / count($groupValues)), 4);
            $context[$group.'.item_count'] = count($groupValues);
        }
        $failed = [];
        foreach ($rules as $rule) {
            $expression = is_array($rule->expression) ? $rule->expression : [];
            $failures = [];
            $passed = $this->matchesQualificationExpression($expression, $context, $failures);
            $parameters = is_array($rule->parameters) ? $rule->parameters : [];
            $minimumAverage = $parameters['min_average'] ?? null;
            if ($passed && is_numeric($minimumAverage)) {
                foreach (['Budaya Mutu', 'Relevansi Pendidikan', 'Relevansi Penelitian'] as $group) {
                    $average = $context[$group.'.item_average'] ?? null;
                    if ($average === null || (float) $average < (float) $minimumAverage) {
                        $passed = false;
                        $failures[] = $group.'.item_average harus minimal '.$minimumAverage;
                    }
                }
            }
            if ($passed) {
                return ['passed' => true, 'status' => (string) ($parameters['status'] ?? $rule->code), 'qualification_rule' => $rule->code, 'validity_years' => (int) ($parameters['validity_years'] ?? 0), 'failed_rules' => [], 'context' => $context];
            }
            $failed[] = ['code' => $rule->code, 'failures' => $failures];
        }
        return ['passed' => $rules === [], 'status' => $rules === [] ? 'calculated' : 'not_qualified', 'qualification_rule' => null, 'validity_years' => 0, 'failed_rules' => $failed, 'context' => $context];
    }

    /** @param array<string, mixed> $expression @param array<string, mixed> $context @param array<int, string> $failures */
    private function matchesQualificationExpression(array $expression, array $context, array &$failures): bool
    {
        if (isset($expression['all']) && is_array($expression['all'])) {
            $result = true;
            foreach ($expression['all'] as $child) {
                $childFailures = [];
                $childPassed = is_array($child) && $this->matchesQualificationExpression($child, $context, $childFailures);
                if (! $childPassed) { $result = false; $failures = [...$failures, ...$childFailures]; }
            }
            return $result;
        }
        if (isset($expression['any']) && is_array($expression['any'])) {
            foreach ($expression['any'] as $child) {
                $childFailures = [];
                if (is_array($child) && $this->matchesQualificationExpression($child, $context, $childFailures)) return true;
            }
            $failures[] = 'Tidak ada kondisi any yang terpenuhi.';
            return false;
        }
        $metric = (string) ($expression['metric'] ?? $expression['field'] ?? '');
        $value = $context[$metric] ?? null;
        if (! is_numeric($value)) { $failures[] = $metric.' tidak tersedia.'; return false; }
        $actual = (float) $value;
        $passed = match (true) {
            array_key_exists('between', $expression) && is_array($expression['between']) => $actual >= (float) ($expression['between'][0] ?? INF) && $actual <= (float) ($expression['between'][1] ?? -INF),
            array_key_exists('gte', $expression) => $actual >= (float) $expression['gte'],
            array_key_exists('lte', $expression) => $actual <= (float) $expression['lte'],
            array_key_exists('gt', $expression) => $actual > (float) $expression['gt'],
            array_key_exists('lt', $expression) => $actual < (float) $expression['lt'],
            array_key_exists('eq', $expression) => abs($actual - (float) $expression['eq']) < 0.000001,
            default => false,
        };
        if (! $passed) $failures[] = $metric.'='.$actual.' tidak memenuhi rule.';
        return $passed;
    }

    private function weightedSum(array $expression, array $values): float
    {
        $weights = is_array($expression['weights'] ?? null) ? $expression['weights'] : $expression;
        $total = 0.0;
        $weightTotal = 0.0;
        foreach ($weights as $key => $weight) {
            if (is_numeric($weight) && is_numeric($values[$key] ?? null)) {
                $total += (float) $values[$key] * (float) $weight;
                $weightTotal += (float) $weight;
            }
        }

        return $weightTotal === 0.0 ? 0.0 : round($total / $weightTotal, 4);
    }

    private function threshold(array $expression, array $values): float
    {
        $value = $values[$expression['field'] ?? ''] ?? null;
        if (! is_numeric($value)) {
            return 0.0;
        }
        $min = isset($expression['min']) && is_numeric($expression['min']) ? (float) $expression['min'] : null;
        $max = isset($expression['max']) && is_numeric($expression['max']) ? (float) $expression['max'] : null;
        $passed = ($min === null || $value >= $min) && ($max === null || $value <= $max);

        return (float) ($passed ? ($expression['pass_score'] ?? 100) : ($expression['fail_score'] ?? 0));
    }

    private function mapping(array $expression, array $values): float
    {
        $field = (string) ($expression['field'] ?? '');
        $value = (string) ($values[$field] ?? '');
        $map = is_array($expression['map'] ?? null) ? $expression['map'] : $expression;

        return is_numeric($map[$value] ?? null) ? (float) $map[$value] : 0.0;
    }

    private function formula(array $expression, array $values): float
    {
        $operation = $expression['operation'] ?? 'average';
        $fields = is_array($expression['fields'] ?? null) ? $expression['fields'] : [];
        $numbers = collect($fields)->map(fn ($field) => $values[$field] ?? null)->filter(fn ($value) => is_numeric($value))->map(fn ($value) => (float) $value);
        if ($numbers->isEmpty()) {
            return 0.0;
        }

        return match ($operation) {
            'sum' => round((float) $numbers->sum(), 4),
            'min' => (float) $numbers->min(),
            'max' => (float) $numbers->max(),
            default => round((float) $numbers->avg(), 4),
        };
    }
}
