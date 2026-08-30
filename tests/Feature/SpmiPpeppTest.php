<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Spmi\EvaluateSpmiRealization;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\SpmiFramework;
use App\Models\SpmiIndicator;
use App\Models\SpmiRealization;
use App\Models\SpmiStandard;
use App\Models\SpmiTarget;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class SpmiPpeppTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_realization_is_evaluated_against_target(): void
    {
        [$user, $indicator, $pt] = $this->createSpmiContext();
        $this->actingAs($user);
        $target = SpmiTarget::query()->create([
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'target_numeric' => 80,
            'status' => 'approved',
            'set_by' => $user->id,
        ]);
        $realization = SpmiRealization::query()->create([
            'spmi_target_id' => $target->id,
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'realization_numeric' => 72,
            'source_type' => 'academic_system',
            'status' => 'verified',
            'recorded_by' => $user->id,
            'verified_by' => $user->id,
            'verified_at' => now(),
        ]);

        $evaluation = app(EvaluateSpmiRealization::class)->handle($realization, $user->id, 'Capaian mendekati target.');

        self::assertSame('partially_met', $evaluation->result);
        self::assertSame('90.0000', (string) $evaluation->achievement_percentage);
        self::assertSame('completed', $evaluation->status);
    }

    public function test_unverified_realization_cannot_enter_evaluation_stage(): void
    {
        [$user, $indicator, $pt] = $this->createSpmiContext();
        $this->actingAs($user);
        $target = SpmiTarget::query()->create([
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'target_numeric' => 80,
        ]);
        $realization = SpmiRealization::query()->create([
            'spmi_target_id' => $target->id,
            'spmi_indicator_id' => $indicator->id,
            'perguruan_tinggi_id' => $pt->id,
            'period_year' => 2026,
            'realization_numeric' => 72,
            'status' => 'submitted',
            'recorded_by' => $user->id,
        ]);

        $this->expectException(ValidationException::class);
        app(EvaluateSpmiRealization::class)->handle($realization, $user->id, 'Tidak boleh dievaluasi.');
    }

    /** @return array{0: User, 1: SpmiIndicator, 2: PerguruanTinggi} */
    private function createSpmiContext(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan SPMI', 'kode' => uniqid('YS')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT SPMI', 'kode_pt' => uniqid('PTS')]);
        ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi SPMI', 'kode_prodi' => uniqid('PRS')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);
        $framework = SpmiFramework::query()->create(['perguruan_tinggi_id' => $pt->id, 'code' => 'SPMI', 'name' => 'Framework SPMI']);
        $standard = SpmiStandard::query()->create(['spmi_framework_id' => $framework->id, 'perguruan_tinggi_id' => $pt->id, 'code' => 'STD-01', 'name' => 'Standar Mutu', 'statement' => 'Standar dasar']);
        $indicator = SpmiIndicator::query()->create(['spmi_standard_id' => $standard->id, 'perguruan_tinggi_id' => $pt->id, 'code' => 'IND-01', 'name' => 'Indikator Mutu']);

        return [$user, $indicator, $pt];
    }
}
