<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\SpmiFramework;
use App\Models\SpmiIndicator;
use App\Models\SpmiRealization;
use App\Models\SpmiStandard;
use App\Models\SpmiTarget;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class QueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lazy_loading_prevention_is_enabled_in_test_environment(): void
    {
        $this->assertTrue(Model::preventsLazyLoading(), 'Model::preventLazyLoading() harus aktif pada environment non-production.');
    }

    public function test_deep_spmi_hierarchy_eager_loading_executes_bounded_queries(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Test', 'kode' => uniqid('YT')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Test', 'kode_pt' => uniqid('PT')]);
        $prodi = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi Test', 'kode_prodi' => uniqid('PR')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        $this->actingAs($user);

        $framework = SpmiFramework::query()->create(['perguruan_tinggi_id' => $pt->id, 'code' => 'SPMI-01', 'name' => 'Framework 2026']);

        for ($s = 1; $s <= 3; $s++) {
            $standard = SpmiStandard::query()->create([
                'spmi_framework_id' => $framework->id,
                'perguruan_tinggi_id' => $pt->id,
                'code' => "STD-{$s}",
                'name' => "Standar {$s}",
                'statement' => "Pernyataan standar {$s}",
            ]);

            for ($i = 1; $i <= 2; $i++) {
                $indicator = SpmiIndicator::query()->create([
                    'spmi_standard_id' => $standard->id,
                    'perguruan_tinggi_id' => $pt->id,
                    'code' => "IND-{$s}-{$i}",
                    'name' => "Indikator {$s}-{$i}",
                ]);

                for ($t = 1; $t <= 2; $t++) {
                    $target = SpmiTarget::query()->create([
                        'spmi_indicator_id' => $indicator->id,
                        'perguruan_tinggi_id' => $pt->id,
                        'period_year' => 2026,
                        'target_numeric' => 100,
                    ]);

                    SpmiRealization::query()->create([
                        'spmi_target_id' => $target->id,
                        'spmi_indicator_id' => $indicator->id,
                        'perguruan_tinggi_id' => $pt->id,
                        'period_year' => 2026,
                        'realization_numeric' => 95,
                        'status' => 'verified',
                        'recorded_by' => $user->id,
                        'verified_by' => $user->id,
                    ]);
                }
            }
        }

        // Warm up tenant scope cache
        $user->accessiblePerguruanTinggiIds();
        $user->accessibleProgramStudiIds();

        DB::enableQueryLog();

        $loadedFramework = SpmiFramework::query()
            ->where('id', $framework->id)
            ->with([
                'standards:id,spmi_framework_id,code,name',
                'standards.indicators:id,spmi_standard_id,code,name',
                'standards.indicators.targets:id,spmi_indicator_id,target_numeric',
                'standards.indicators.targets.realizations:id,spmi_target_id,realization_numeric,status',
            ])
            ->first();

        $queryCount = count(DB::getQueryLog());

        $this->assertNotNull($loadedFramework);
        $this->assertSame(5, $queryCount, "Deep eager loading untuk 4-level SPMI tree harus dieksekusi tepat dalam 5 query (1 query per level).");

        $totalRealizations = 0;
        foreach ($loadedFramework->standards as $std) {
            foreach ($std->indicators as $ind) {
                foreach ($ind->targets as $tgt) {
                    foreach ($tgt->realizations as $rel) {
                        $totalRealizations++;
                        $this->assertNotNull($rel->realization_numeric);
                    }
                }
            }
        }

        $this->assertSame(12, $totalRealizations);
    }
}

