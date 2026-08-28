<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\InstrumentRegistry\ImportCanonicalInstrument;
use PHPUnit\Framework\TestCase;

final class CanonicalImportV2Test extends TestCase
{
    public function test_preview_accepts_extended_json_rows(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'sqm-import-');
        self::assertIsString($path);
        file_put_contents($path, json_encode([
            'rows' => [
                ['entity_type' => 'scale', 'code' => 'SCALE-1-4', 'title' => 'Skala LAM'],
                ['entity_type' => 'scale_option', 'code' => 'SCORE-4', 'title' => 'Sangat Baik', 'scale_code' => 'SCALE-1-4', 'numeric_value' => 4],
                ['entity_type' => 'rubric', 'code' => 'R-4', 'title' => 'Sangat Baik', 'description' => 'Descriptor skor 4.'],
                ['entity_type' => 'threshold', 'code' => 'T-4', 'title' => 'Threshold skor 4', 'indicator_code' => 'IND-1', 'comparison' => 'gte', 'target_value' => 5],
                ['entity_type' => 'qualification_rule', 'code' => 'QUAL-3', 'rule_type' => 'status_qualification', 'expression' => ['all' => [['metric' => 'final_score', 'gte' => 321]]]],
            ],
        ], JSON_THROW_ON_ERROR));

        try {
            $preview = app(ImportCanonicalInstrument::class)->preview($path);
            self::assertSame([], $preview['errors']);
            self::assertCount(5, $preview['rows']);
        } finally {
            @unlink($path);
        }
    }
}
