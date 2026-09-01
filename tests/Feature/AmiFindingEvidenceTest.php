<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AmiFindingEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ami_finding_can_link_cloud_evidence(): void
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

        $finding = AmiFinding::query()->create([
            'ami_cycle_id' => $cycle->id,
            'code' => 'FND-001',
            'classification' => 'nonconformity',
            'severity' => 'major',
            'condition' => 'RPS belum diperbarui sesuai OBE.',
            'status' => 'open',
            'reported_by' => $user->id,
        ]);

        $evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $user->id,
            'code' => 'EVD-RPS-2026',
            'title' => 'Dokumen RPS Prodi Informatika',
        ]);

        EvidenceLink::query()->create([
            'evidence_id' => $evidence->id,
            'linkable_type' => AmiFinding::class,
            'linkable_id' => $finding->id,
            'relation_type' => 'audit_evidence',
            'citation_page' => 'Hlm. 4-6',
            'citation_note' => 'Format RPS masih menggunakan template lama',
            'is_required' => true,
        ]);

        $finding->refresh();

        self::assertCount(1, $finding->evidenceLinks);
        self::assertSame($evidence->id, $finding->evidenceLinks->first()->evidence->id);
        self::assertSame('Hlm. 4-6', $finding->evidenceLinks->first()->citation_page);
    }
}
