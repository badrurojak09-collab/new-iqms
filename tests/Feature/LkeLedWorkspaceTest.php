<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LkeLedWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_lke_led_workspace_page(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $yayasan = Yayasan::query()->create([
            'kode' => 'YYS-01',
            'nama' => 'Yayasan Demo',
        ]);

        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->getKey(),
            'kode_pt' => 'PT-01',
            'nama_pt' => 'Institut Teknologi Demo',
            'status' => 'active',
        ]);

        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
            'kode_prodi' => 'SI-01',
            'nama_prodi' => 'Sistem Informasi',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
        ]);
        $user->assignRole('pt_admin');

        $body = AccreditationBody::query()->create([
            'code' => 'LAM-INFOKOM',
            'name' => 'LAM-INFOKOM',
            'kind' => 'LAM-INFOKOM',
            'status' => 'active',
        ]);

        $family = InstrumentFamily::query()->create([
            'accreditation_body_id' => $body->getKey(),
            'code' => 'LAM-INFOKOM-APS',
            'name' => 'Instrumen APS',
            'scope_type' => 'program_study',
        ]);

        $version = InstrumentVersion::query()->create([
            'instrument_family_id' => $family->getKey(),
            'version_label' => 'LAM-INFOKOM 2.1',
            'status' => 'active',
        ]);

        $acc = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
            'program_studi_id' => $prodi->getKey(),
            'instrument_version_id' => $version->getKey(),
            'code' => 'AKR-TEST-WS',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Workspace Test',
            'status' => 'in_progress',
            'owner_id' => $user->getKey(),
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Filament\Pages\LkeLedWorkspace::class)
            ->assertSuccessful()
            ->assertSee('Workspace LKE dan LED')
            ->assertSee('AKR-TEST-WS');
    }

    public function test_calculate_score_repeatedly_does_not_throw_duplicate_entry_exception(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $yayasan = Yayasan::query()->create(['kode' => 'YYS-02', 'nama' => 'Yayasan Demo 2']);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->getKey(), 'kode_pt' => 'PT-02', 'nama_pt' => 'Institut 2', 'status' => 'active']);
        $prodi = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->getKey(), 'kode_prodi' => 'IF-02', 'nama_prodi' => 'Informatika', 'jenjang' => 'S1', 'status' => 'active']);
        $user = User::factory()->create(['perguruan_tinggi_id' => $pt->getKey()]);
        $user->assignRole('pt_admin');

        $body = AccreditationBody::query()->create(['code' => 'BAN-PT', 'name' => 'BAN-PT', 'kind' => 'national', 'status' => 'active']);
        $family = InstrumentFamily::query()->create(['accreditation_body_id' => $body->getKey(), 'code' => 'BAN-PT-IAPT', 'name' => 'IAPT', 'scope_type' => 'institution']);
        $version = InstrumentVersion::query()->create(['instrument_family_id' => $family->getKey(), 'version_label' => 'IAPT 3.0', 'status' => 'active']);

        $acc = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
            'program_studi_id' => $prodi->getKey(),
            'instrument_version_id' => $version->getKey(),
            'code' => 'AKR-SCORE-TEST',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Score Test',
            'status' => 'in_progress',
            'owner_id' => $user->getKey(),
        ]);

        // Call calculateScore twice in the Livewire component
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Filament\Pages\LkeLedWorkspace::class)
            ->call('selectAccreditation', $acc->getKey())
            ->call('calculateScore')
            ->assertNotified('Simulasi Skor Berhasil')
            ->call('calculateScore')
            ->assertNotified('Simulasi Skor Berhasil')
            ->assertSuccessful();

        $this->assertDatabaseCount('accreditation_score_snapshots', 1);
    }
}
