<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\UserTenantScope;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTenantScopeResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_label_resolves_yayasan(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Scope Test']);
        $scope = UserTenantScope::query()->create([
            'user_id' => User::factory()->create()->id,
            'scope_type' => 'yayasan',
            'scope_id' => $yayasan->id,
            'role_id' => Role::query()->firstOrCreate(['name' => 'scope-test-role', 'guard_name' => 'web'])->id,
        ]);

        self::assertSame('Yayasan Scope Test', $scope->scopeLabel());
    }

    public function test_scope_label_resolves_program_studi(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Scope Prodi']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Scope Prodi',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);
        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Sistem Informasi Scope',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);
        $scope = UserTenantScope::query()->create([
            'user_id' => User::factory()->create()->id,
            'scope_type' => 'program_studi',
            'scope_id' => $prodi->id,
            'role_id' => Role::query()->firstOrCreate(['name' => 'scope-prodi-role', 'guard_name' => 'web'])->id,
        ]);

        self::assertSame('Sistem Informasi Scope', $scope->scopeLabel());
    }
}

