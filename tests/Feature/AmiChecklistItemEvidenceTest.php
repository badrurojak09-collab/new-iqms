<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AmiChecklistItem;
use App\Models\AmiCycle;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AmiChecklistItemEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ami_checklist_item_can_link_cloud_evidence(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan AMI', 'kode' => uniqid('YA')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT AMI', 'kode_pt' => uniqid('PTA')]);
        $prodi = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Informatika', 'kode_prodi' => 'IF']);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        $this->actingAs($user);

        $cycle = AmiCycle::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'code' => 'AMI-2026-1',
            'name' => 'Siklus AMI 2026',
            'period_year' => 2026,
            'status' => 'active',
        ]);

        $checklistItem = AmiChecklistItem::query()->create([
            'ami_cycle_id' => $cycle->id,
            'code' => 'CHK-01',
            'question' => 'Apakah kurikulum program studi ditinjau secara berkala?',
            'response_type' => 'boolean',
            'response_status' => 'completed',
            'evidence_required' => true,
        ]);

        $evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $user->id,
            'code' => 'EVD-KUR-2026',
            'title' => 'SK Peninjauan Kurikulum 2026',
        ]);

        EvidenceLink::query()->create([
            'evidence_id' => $evidence->id,
            'linkable_type' => AmiChecklistItem::class,
            'linkable_id' => $checklistItem->id,
            'relation_type' => 'audit_evidence',
            'citation_page' => 'Hlm. 2-3',
            'citation_note' => 'SK Dekan tentang Peninjauan Kurikulum',
            'is_required' => true,
        ]);

        $checklistItem->refresh();

        self::assertCount(1, $checklistItem->evidenceLinks);
        self::assertSame($evidence->id, $checklistItem->evidenceLinks->first()->evidence->id);
        self::assertSame('Hlm. 2-3', $checklistItem->evidenceLinks->first()->citation_page);
    }
}
