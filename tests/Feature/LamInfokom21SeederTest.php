<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccreditationBody;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentRubric;
use App\Models\AssessmentScale;
use App\Models\AssessmentThreshold;
use App\Models\InstrumentFamily;
use App\Models\InstrumentScoringRule;
use App\Models\InstrumentVersion;
use Database\Seeders\LamInfokom21CriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LamInfokom21SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_lam_infokom_21_seeder_populates_all_criteria_and_scoring_system(): void
    {
        $this->seed(LamInfokom21CriteriaSeeder::class);

        $body = AccreditationBody::query()->where('code', 'LAM-INFOKOM')->first();
        self::assertNotNull($body);
        self::assertSame('Lembaga Akreditasi Mandiri Informatika dan Komputer', $body->name);

        $family = InstrumentFamily::query()->where('code', 'LAM-INFOKOM-APS')->first();
        self::assertNotNull($family);

        $version = InstrumentVersion::query()->where('version_label', 'LAM INFOKOM 2.1 - 2025 - Sarjana')->first();
        self::assertNotNull($version);

        $scale = AssessmentScale::query()->where('instrument_version_id', $version->id)->first();
        self::assertNotNull($scale);
        self::assertSame(4, $scale->options()->count());

        $criteria = AssessmentCriterion::query()->where('instrument_version_id', $version->id)->get();
        self::assertCount(9, $criteria);

        $elements = AssessmentElement::query()
            ->whereHas('criterion', fn ($q) => $q->where('instrument_version_id', $version->id))
            ->get();
        self::assertCount(82, $elements);

        $totalWeight = $elements->sum('weight');
        self::assertEqualsWithDelta(400.0, (float) $totalWeight, 0.01);

        $rubricsCount = AssessmentRubric::query()->where('instrument_version_id', $version->id)->count();
        self::assertSame(82 * 4, $rubricsCount);

        $thresholds = AssessmentThreshold::query()->where('instrument_version_id', $version->id)->get();
        self::assertCount(4, $thresholds);

        $rules = InstrumentScoringRule::query()->where('instrument_version_id', $version->id)->get();
        self::assertCount(2, $rules);

        // Test idempotency (re-running the seeder does not create duplicates or fail)
        $this->seed(LamInfokom21CriteriaSeeder::class);
        self::assertSame(82, AssessmentElement::query()
            ->whereHas('criterion', fn ($q) => $q->where('instrument_version_id', $version->id))
            ->count());
    }
}
