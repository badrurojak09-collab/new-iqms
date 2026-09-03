<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Lkps\LkpsDatasetService;
use App\Domain\Lkps\LkpsImportService;
use App\Filament\Pages\LkeLedWorkspace;
use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\LkpsDataset;
use App\Models\LkpsTemplate;
use App\Models\LkpsTemplateColumn;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

final class LkpsDatasetWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Accreditation $accreditation;
    private LkpsTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $yayasan = Yayasan::query()->create(['kode' => 'YYS-01', 'nama' => 'Yayasan Uji Mutu']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Institut Teknologi Uji',
            'kode_pt' => 'ITU-001',
            'jenis' => 'institut',
            'status' => 'active',
        ]);
        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Informatika Uji',
            'kode_prodi' => 'IF-001',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $this->user->assignRole('pt_admin');

        $body = AccreditationBody::query()->create([
            'code' => 'LAM-INFOKOM',
            'name' => 'LAM-INFOKOM',
            'kind' => 'LAM-INFOKOM',
            'status' => 'active',
        ]);
        $family = InstrumentFamily::query()->create([
            'accreditation_body_id' => $body->id,
            'code' => 'TEST-LAM-IF',
            'name' => 'Keluarga Instrumen Uji',
            'scope_type' => 'program_study',
        ]);
        $version = InstrumentVersion::query()->create([
            'instrument_family_id' => $family->id,
            'version_label' => 'v1.0-test',
            'status' => 'active',
        ]);

        $this->template = LkpsTemplate::query()->create([
            'instrument_version_id' => $version->id,
            'code' => 'T-DOSEN-TEST',
            'name' => 'Tabel Profil Dosen Tetap',
            'row_definition' => ['periods' => ['2024', '2025']],
            'is_required' => true,
            'sort_order' => 1,
        ]);

        LkpsTemplateColumn::query()->create([
            'lkps_template_id' => $this->template->id,
            'column_key' => 'tahun',
            'label' => 'Tahun',
            'data_type' => 'string',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        LkpsTemplateColumn::query()->create([
            'lkps_template_id' => $this->template->id,
            'column_key' => 'jumlah_dosen',
            'label' => 'Jumlah Dosen Tetap',
            'data_type' => 'integer',
            'unit' => 'orang',
            'min_value' => 0,
            'max_value' => 500,
            'is_required' => true,
            'sort_order' => 2,
        ]);

        LkpsTemplateColumn::query()->create([
            'lkps_template_id' => $this->template->id,
            'column_key' => 'publikasi_dosen',
            'label' => 'Rasio Publikasi',
            'data_type' => 'decimal',
            'decimal_scale' => 2,
            'is_required' => false,
            'sort_order' => 3,
        ]);

        $this->accreditation = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'instrument_version_id' => $version->id,
            'code' => 'AKR-TEST-2026',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Program Studi Informatika Uji',
            'status' => 'in_progress',
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_lkps_dataset_service_crud_and_validation(): void
    {
        $service = app(LkpsDatasetService::class);

        $dataset = $service->getOrCreateDataset($this->accreditation, $this->template);
        $this->assertInstanceOf(LkpsDataset::class, $dataset);
        $this->assertCount(2, $dataset->rows_data); // Initialized from periods 2024 and 2025

        // Save with valid rows
        $rows = [
            ['tahun' => '2024', 'jumlah_dosen' => 15, 'publikasi_dosen' => 2.50],
            ['tahun' => '2025', 'jumlah_dosen' => 18, 'publikasi_dosen' => 3.10],
        ];

        $saved = $service->saveDataset($this->accreditation, $this->template, $rows, $this->user->id);
        $this->assertSame(LkpsDataset::STATUS_APPROVED, $saved->status);
        $this->assertEmpty($saved->validation_errors);
        $this->assertSame(33, (int) ($saved->summary_metrics['column_totals']['jumlah_dosen'] ?? 0));

        // Save with validation error (negative number for integer)
        $invalidRows = [
            ['tahun' => '2024', 'jumlah_dosen' => -5, 'publikasi_dosen' => 2.50],
        ];
        $savedInvalid = $service->saveDataset($this->accreditation, $this->template, $invalidRows, $this->user->id);
        $this->assertSame(LkpsDataset::STATUS_DRAFT, $savedInvalid->status);
        $this->assertNotEmpty($savedInvalid->validation_errors);

        // Progress calculation
        $progress = $service->calculateOverallLkpsProgress($this->accreditation);
        $this->assertGreaterThanOrEqual(0.0, $progress);
    }

    public function test_lkps_import_service_parses_csv_and_commits(): void
    {
        $importService = app(LkpsImportService::class);

        $csvContent = "Tahun,Jumlah Dosen Tetap,Rasio Publikasi\n2024,20,2.75\n2025,25,3.50\n";
        $file = UploadedFile::fake()->createWithContent('import_dosen.csv', $csvContent);

        $reconcile = $importService->parseAndReconcile($file, $this->template);
        $this->assertSame(2, $reconcile['raw_rows_count']);
        $this->assertSame(2, $reconcile['validation']['summary']['valid_rows']);

        $committed = $importService->commitImport($this->accreditation, $this->template, $reconcile['validation']['rows'], $this->user->id);
        $this->assertSame(LkpsDataset::STATUS_APPROVED, $committed->status);
        $this->assertCount(2, $committed->rows_data);
    }

    public function test_workspace_livewire_interacts_with_lkps_tab(): void
    {
        Livewire::actingAs($this->user)
            ->test(LkeLedWorkspace::class, ['accreditation' => $this->accreditation->id])
            ->assertSuccessful()
            ->call('setWorkspaceTab', 'lkps')
            ->assertSet('activeWorkspaceTab', 'lkps')
            ->assertSet('selectedLkpsTemplateId', $this->template->id)
            ->assertSee('Tabel Profil Dosen Tetap')
            ->call('addLkpsRow')
            ->call('saveLkpsDataset')
            ->assertSuccessful();
    }
}
