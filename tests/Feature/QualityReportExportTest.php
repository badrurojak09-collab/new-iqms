<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\RtmMeeting;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class QualityReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_export_rtm_minutes_html(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Mutu', 'kode' => uniqid('YM')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Mutu', 'kode_pt' => uniqid('PTM')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        $meeting = RtmMeeting::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'RTM-2026-01',
            'title' => 'Rapat Tinjauan Manajemen Semester Ganjil 2026',
            'held_at' => now(),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->get(route('rtm-meetings.export-minutes', ['meeting' => $meeting->id]));

        $response->assertOk();
        $response->assertSee('RISALAH RAPAT TINJAUAN MANAJEMEN');
        $response->assertSee('RTM-2026-01');
    }

    public function test_can_export_ami_summary_html(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan AMI', 'kode' => uniqid('YA')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT AMI', 'kode_pt' => uniqid('PTA')]);
        $prodi = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Informatika', 'kode_prodi' => 'IF']);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        $cycle = AmiCycle::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'code' => 'AMI-2026-1',
            'name' => 'Siklus AMI 2026',
            'period_year' => 2026,
            'status' => 'completed',
        ]);

        AmiFinding::query()->create([
            'ami_cycle_id' => $cycle->id,
            'code' => 'FND-01',
            'classification' => 'nonconformity',
            'severity' => 'major',
            'condition' => 'Kondisi RPS belum terstandar',
            'status' => 'open',
            'reported_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('ami-cycles.export-summary', ['cycle' => $cycle->id]));

        $response->assertOk();
        $response->assertSee('LAPORAN HASIL AUDIT MUTU INTERNAL');
        $response->assertSee('AMI-2026-1');
        $response->assertSee('FND-01');
    }
}
