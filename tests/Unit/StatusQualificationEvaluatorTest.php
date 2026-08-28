<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Models\InstrumentScoringRule;
use PHPUnit\Framework\TestCase;

final class StatusQualificationEvaluatorTest extends TestCase
{
    public function test_qualification_rule_returns_highest_validity_status_when_all_gates_pass(): void
    {
        $engine = new RuntimeScoringEngine();
        $method = new \ReflectionMethod($engine, 'evaluateQualifications');
        $method->setAccessible(true);
        $rules = [
            $this->rule('QUAL-3', ['all' => [['metric' => 'final_score', 'gte' => 321]]], ['min_average' => 3.2, 'validity_years' => 3, 'status' => 'unggul_3_tahun']),
            $this->rule('QUAL-5', ['all' => [['metric' => 'final_score', 'gte' => 361]]], ['min_average' => 3.2, 'validity_years' => 5, 'status' => 'unggul_5_tahun']),
        ];
        $evaluated = [
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Budaya Mutu'],
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Relevansi Pendidikan'],
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Relevansi Penelitian'],
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Relevansi Pendidikan'],
        ];

        $result = $method->invoke($engine, $rules, 4.0, $evaluated);

        self::assertTrue($result['passed']);
        self::assertSame('unggul_5_tahun', $result['status']);
        self::assertSame(5, $result['validity_years']);
        self::assertSame(400.0, $result['context']['final_score']);
    }

    public function test_qualification_rule_fails_when_item_minimum_gate_is_not_met(): void
    {
        $engine = new RuntimeScoringEngine();
        $method = new \ReflectionMethod($engine, 'evaluateQualifications');
        $method->setAccessible(true);
        $rule = $this->rule('QUAL-3', ['all' => [['metric' => 'final_score', 'gte' => 321], ['metric' => 'Budaya Mutu.item_min', 'gte' => 3]]], ['min_average' => 3.2, 'validity_years' => 3, 'status' => 'unggul_3_tahun']);
        $evaluated = [
            ['score' => 2, 'weight' => 100, 'aggregation_key' => 'Budaya Mutu'],
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Relevansi Pendidikan'],
            ['score' => 4, 'weight' => 100, 'aggregation_key' => 'Relevansi Penelitian'],
        ];

        $result = $method->invoke($engine, [$rule], 3.33, $evaluated);

        self::assertFalse($result['passed']);
        self::assertSame('not_qualified', $result['status']);
        self::assertNotEmpty($result['failed_rules']);
    }

    private function rule(string $code, array $expression, array $parameters): InstrumentScoringRule
    {
        $rule = new InstrumentScoringRule();
        $rule->code = $code;
        $rule->rule_type = 'status_qualification';
        $rule->expression = $expression;
        $rule->parameters = $parameters;
        return $rule;
    }
}
