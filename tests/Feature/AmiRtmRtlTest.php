<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ami\AmiCycleLifecycleService;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\RtlAction;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\User;
use App\Models\Yayasan;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AmiRtmRtlTest extends TestCase
{
    use RefreshDatabase;

    public function test_finding_flows_from_ami_to_rtm_and_rtl(): void
    {
        [$user, $pt] = $this->context();
        $this->actingAs($user);
        $cycle = AmiCycle::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'AMI-2026',
            'name' => 'Audit Mutu Internal 2026',
            'period_year' => 2026,
            'scope_type' => 'institution',
            'status' => 'in_progress',
            'coordinator_id' => $user->id,
        ]);
        $finding = AmiFinding::query()->create([
            'ami_cycle_id' => $cycle->id,
            'reported_by' => $user->id,
            'code' => 'F-001',
            'classification' => 'nonconformity',
            'severity' => 'major',
            'condition' => 'Bukti pelaksanaan belum lengkap.',
            'recommendation' => 'Lengkapi dan verifikasi bukti.',
        ]);
        $meeting = RtmMeeting::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'ami_cycle_id' => $cycle->id,
            'code' => 'RTM-2026-01',
            'title' => 'RTM Hasil AMI 2026',
            'status' => 'completed',
            'chair_id' => $user->id,
        ]);
        $decision = RtmDecision::query()->create([
            'rtm_meeting_id' => $meeting->id,
            'ami_finding_id' => $finding->id,
            'code' => 'D-001',
            'decision' => 'Menetapkan perbaikan evidence dalam 30 hari.',
            'status' => 'approved',
        ]);
        $rtl = RtlAction::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'ami_finding_id' => $finding->id,
            'rtm_decision_id' => $decision->id,
            'owner_id' => $user->id,
            'code' => 'RTL-001',
            'title' => 'Melengkapi evidence',
            'action_plan' => 'Upload dokumen dan minta verifikasi LPM.',
            'progress_percent' => 50,
            'status' => 'in_progress',
        ]);

        self::assertTrue($finding->refresh()->cycle->is($cycle));
        self::assertTrue($decision->refresh()->finding->is($finding));
        self::assertSame('in_progress', $rtl->status);
        self::assertSame(50, $rtl->progress_percent);
    }

    public function test_ami_cycle_lifecycle_is_guarded_and_audited(): void
    {
        [$user, $pt] = $this->context();
        $cycle = AmiCycle::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'code' => 'AMI-LIFE-2026',
            'name' => 'Siklus Lifecycle AMI',
            'period_year' => 2026,
            'scope_type' => 'institution',
            'status' => 'draft',
        ]);

        Permission::query()->firstOrCreate(['name' => 'manage ami', 'guard_name' => 'web']);
        Permission::query()->firstOrCreate(['name' => 'review ami', 'guard_name' => 'web']);
        $user->givePermissionTo(['manage ami', 'review ami']);

        $this->actingAs($user);
        $service = app(AmiCycleLifecycleService::class);
        $service->start($cycle, $user);
        $service->complete($cycle->refresh(), $user);
        $service->close($cycle->refresh(), $user);

        $this->assertSame('closed', $cycle->refresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'ami.cycle.status_changed',
            'auditable_type' => (new AmiCycle)->getMorphClass(),
            'auditable_id' => $cycle->id,
        ]);
    }

    /** @return array{0: User, 1: PerguruanTinggi} */
    private function context(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan AMI', 'kode' => uniqid('YA')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT AMI', 'kode_pt' => uniqid('PT')]);
        ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi AMI', 'kode_prodi' => uniqid('PAMI')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        return [$user, $pt];
    }
}
