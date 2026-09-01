<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\DocumentOutput\Generators\LedDocumentRenderer;
use App\Domain\DocumentOutput\Generators\LkpsSpreadsheetGenerator;
use App\Domain\DocumentOutput\Services\AccreditationDocumentExporter;
use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccreditationExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Accreditation $accreditation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $yayasan = \App\Models\Yayasan::query()->create([
            'kode' => 'YYS-01',
            'nama' => 'Yayasan Pendidikan Demo',
        ]);

        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->getKey(),
            'kode_pt' => 'PT-DEMO',
            'nama_pt' => 'Universitas Teknologi Demo',
            'status' => 'active',
        ]);

        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
            'kode_prodi' => 'TI-01',
            'nama_prodi' => 'Teknik Informatika',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        $this->admin = User::factory()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
        ]);
        $this->admin->assignRole('pt_admin');

        $body = AccreditationBody::query()->create([
            'code' => 'LAM-INFOKOM',
            'name' => 'Lembaga Akreditasi Mandiri Informatika',
            'kind' => 'LAM-INFOKOM',
            'status' => 'active',
        ]);

        $family = InstrumentFamily::query()->create([
            'accreditation_body_id' => $body->getKey(),
            'code' => 'LAM-INFOKOM-APS',
            'name' => 'Instrumen APS LAM-INFOKOM',
            'scope_type' => 'program_study',
        ]);

        $version = InstrumentVersion::query()->create([
            'instrument_family_id' => $family->getKey(),
            'version_label' => 'LAM-INFOKOM 2.1 - Sarjana',
            'status' => 'active',
        ]);

        $this->accreditation = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->getKey(),
            'program_studi_id' => $prodi->getKey(),
            'instrument_version_id' => $version->getKey(),
            'code' => 'AKR-TEST-01',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Uji Coba TI',
            'status' => 'in_progress',
            'owner_id' => $this->admin->getKey(),
        ]);
    }

    public function test_can_export_score_simulation(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->getKey(),
            'type' => 'score-simulation',
        ]));

        $response->assertOk();
        $response->assertSee('MATRIKS SIMULASI SKOR AKREDITASI');
    }

    public function test_can_export_led_html(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->getKey(),
            'type' => 'led-html',
        ]));

        $response->assertOk();
        $response->assertSee('LAPORAN EVALUASI DIRI (LED)');
    }

    public function test_can_export_lkps_html(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->getKey(),
            'type' => 'lkps-html',
        ]));

        $response->assertOk();
        $response->assertSee('LAPORAN KINERJA (LKPS / LKPT)');
    }

    public function test_can_export_led_docx(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->getKey(),
            'type' => 'led-docx',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=LED-AKR-TEST-01.docx');
    }

    public function test_can_export_lkps_xlsx(): void
    {
        $response = $this->actingAs($this->admin)->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->getKey(),
            'type' => 'lkps-xlsx',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=LKPS-AKR-TEST-01.xlsx');
    }
}
