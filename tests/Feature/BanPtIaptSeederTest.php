<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AccreditationBody;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentRubric;
use App\Models\AssessmentThreshold;
use App\Models\InstrumentFamily;
use App\Models\InstrumentScoringRule;
use App\Models\InstrumentVersion;
use Database\Seeders\BanPtIaptCriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BanPtIaptSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ban_pt_iapt_seeder_populates_all_9_criteria_and_scoring_system(): void
    {
        // Jalankan seeder pertama kali
        $this->seed(BanPtIaptCriteriaSeeder::class);

        $body = AccreditationBody::query()->where('code', 'BAN-PT')->first();
        $this->assertNotNull($body);
        $this->assertSame('Badan Akreditasi Nasional Perguruan Tinggi', $body->name);

        $family = InstrumentFamily::query()->where('code', 'BAN-PT-IAPT')->first();
        $this->assertNotNull($family);
        $this->assertSame('institution', $family->scope_type);

        $version = InstrumentVersion::query()
            ->where('instrument_family_id', $family->getKey())
            ->where('version_label', 'BAN-PT IAPT 3.0 - Perguruan Tinggi')
            ->first();
        $this->assertNotNull($version);

        // Kriteria
        $criteria = AssessmentCriterion::query()->where('instrument_version_id', $version->getKey())->get();
        $this->assertCount(11, $criteria); // 9 Kriteria Utama + Kondisi Eksternal + Analisis Pengembangan

        // Elemen
        $elements = AssessmentElement::query()
            ->whereIn('assessment_criterion_id', $criteria->pluck('id'))
            ->get();
        $this->assertCount(31, $elements);

        // Total bobot harus tepat 400.0
        $totalWeight = (float) $elements->sum('weight');
        $this->assertEqualsWithDelta(400.0, $totalWeight, 0.001);

        // Rubrik skor (31 elemen * 4 skor = 124 rubrik)
        $rubrics = AssessmentRubric::query()->where('instrument_version_id', $version->getKey())->get();
        $this->assertCount(124, $rubrics);

        // Threshold status akreditasi (Tidak Terakreditasi, Baik, Baik Sekali, Unggul)
        $thresholds = AssessmentThreshold::query()->where('instrument_version_id', $version->getKey())->get();
        $this->assertCount(4, $thresholds);

        // Scoring rules kualifikasi Unggul & Baik Sekali
        $rules = InstrumentScoringRule::query()->where('instrument_version_id', $version->getKey())->get();
        $this->assertCount(2, $rules);

        // Uji Idempotensi: Jalankan seeder kedua kali tanpa error dan tanpa duplikasi
        $this->seed(BanPtIaptCriteriaSeeder::class);

        $this->assertCount(31, AssessmentElement::query()->whereIn('assessment_criterion_id', $criteria->pluck('id'))->get());
        $this->assertCount(124, AssessmentRubric::query()->where('instrument_version_id', $version->getKey())->get());
    }
}
