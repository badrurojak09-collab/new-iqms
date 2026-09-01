<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\PerguruanTinggi;
use App\Models\SpmiFramework;
use App\Models\SpmiIndicator;
use App\Models\SpmiRealization;
use App\Models\SpmiStandard;
use App\Models\SpmiTarget;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SpmiRealizationEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_spmi_realization_can_link_cloud_evidence(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan SPMI', 'kode' => uniqid('YS')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT SPMI', 'kode_pt' => uniqid('PTS')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        $this->actingAs($user);

        $framework = SpmiFramework::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'SPMI-2026',
            'name' => 'Framework 2026',
        ]);
        $standard = SpmiStandard::query()->create([
            'spmi_framework_id' => $framework->id,
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'STD-01',
            'name' => 'Standar Penelitian',
            'statement' => 'Setiap dosen melakukan penelitian.',
        ]);
        $indicator = SpmiIndicator::query()->create([
            'spmi_standard_id' => $standard->id,
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'IND-01',
            'name' => 'Publikasi per Dosen',
        ]);
        $target = SpmiTarget::query()->create([
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'target_numeric' => 2.0,
            'set_by' => $user->id,
        ]);
        $realization = SpmiRealization::query()->create([
            'spmi_target_id' => $target->id,
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'realization_numeric' => 2.5,
            'source_type' => 'internal',
            'status' => 'draft',
            'recorded_by' => $user->id,
        ]);

        $evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $user->id,
            'code' => 'EVD-PUB-2026',
            'title' => 'Laporan Publikasi Dosen 2026',
        ]);

        EvidenceLink::query()->create([
            'evidence_id' => $evidence->id,
            'linkable_type' => SpmiRealization::class,
            'linkable_id' => $realization->id,
            'relation_type' => 'supports',
            'citation_page' => 'Hlm. 10-15',
            'citation_note' => 'Rekap publikasi terindeks SINTA',
            'is_required' => true,
        ]);

        $realization->refresh();

        self::assertCount(1, $realization->evidenceLinks);
        self::assertSame($evidence->id, $realization->evidenceLinks->first()->evidence->id);
        self::assertSame('Hlm. 10-15', $realization->evidenceLinks->first()->citation_page);
    }
}
