<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\SpmiFramework;
use App\Models\SpmiStandard;
use App\Models\User;
use App\Models\Yayasan;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_perguruan_tinggi_uses_id_for_perguruan_tinggi_model(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Test', 'kode' => 'YT']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Test',
            'kode_pt' => 'PT-T',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);

        $query = TenantQuery::forPerguruanTinggi(PerguruanTinggi::query(), $user);
        $sql = str_replace(['`', '"'], '', $query->toSql());

        self::assertStringContainsString('perguruan_tinggi.id in', $sql);
        self::assertStringNotContainsString('perguruan_tinggi.perguruan_tinggi_id', $sql);
    }

    public function test_for_perguruan_tinggi_uses_foreign_key_for_child_models(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Test 2', 'kode' => 'YT2']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Test 2',
            'kode_pt' => 'PT-T2',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);

        $query = TenantQuery::forPerguruanTinggi(SpmiFramework::query(), $user);
        $sql = str_replace(['`', '"'], '', $query->toSql());

        self::assertStringContainsString('spmi_frameworks.perguruan_tinggi_id in', $sql);
    }

    public function test_for_program_studi_uses_id_for_program_studi_model(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Test 3', 'kode' => 'YT3']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Test 3',
            'kode_pt' => 'PT-T3',
        ]);
        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Prodi Test',
            'kode_prodi' => 'PR-T',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $user->programStudis()->attach($prodi->id, ['peran' => 'kaprodi']);

        $query = TenantQuery::forProgramStudi(ProgramStudi::query(), $user);
        $sql = str_replace(['`', '"'], '', $query->toSql());

        self::assertStringContainsString('program_studi.id in', $sql);
        self::assertStringNotContainsString('program_studi.program_studi_id', $sql);
    }

    public function test_for_program_studi_uses_foreign_key_for_child_models(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Test 4', 'kode' => 'YT4']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Test 4',
            'kode_pt' => 'PT-T4',
        ]);
        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Prodi Test 4',
            'kode_prodi' => 'PR-T4',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $user->programStudis()->attach($prodi->id, ['peran' => 'kaprodi']);

        $query = TenantQuery::forProgramStudi(SpmiStandard::query(), $user);
        $sql = str_replace(['`', '"'], '', $query->toSql());

        self::assertStringContainsString('spmi_standards.program_studi_id in', $sql);
    }
}
