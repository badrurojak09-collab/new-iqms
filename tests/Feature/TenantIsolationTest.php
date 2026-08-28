<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_unrelated_tenant(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan A', 'kode' => 'YA']);
        $otherYayasan = Yayasan::query()->create(['nama' => 'Yayasan B', 'kode' => 'YB']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT A',
            'kode_pt' => 'PTA',
        ]);
        $otherPt = PerguruanTinggi::query()->create([
            'yayasan_id' => $otherYayasan->id,
            'nama_pt' => 'PT B',
            'kode_pt' => 'PTB',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);

        self::assertTrue($user->canAccessPerguruanTinggi($pt));
        self::assertFalse($user->canAccessPerguruanTinggi($otherPt));

        $this->expectException(AccessDeniedHttpException::class);
        app(TenantContext::class)->set($user, $otherPt->id);
    }

    public function test_user_can_access_assigned_program_study_only(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan A', 'kode' => 'YA2']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT A',
            'kode_pt' => 'PTA2',
        ]);
        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Prodi A',
            'kode_prodi' => 'PRA',
        ]);
        $otherProdi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Prodi B',
            'kode_prodi' => 'PRB',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $user->programStudis()->attach($prodi->id, ['peran' => 'kaprodi']);

        self::assertTrue($user->canAccessProgramStudi($prodi));
        self::assertFalse($user->canAccessProgramStudi($otherProdi));
    }
}
