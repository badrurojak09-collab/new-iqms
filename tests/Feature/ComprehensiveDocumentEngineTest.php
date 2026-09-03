<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\InstrumentFamily;
use App\Models\InstrumentNode;
use App\Models\InstrumentVersion;
use App\Models\LkpsTemplate;
use App\Models\LkpsTemplateColumn;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\RtmParticipant;
use App\Models\User;
use App\Models\Yayasan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ComprehensiveDocumentEngineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Accreditation $accreditation;
    private RtmMeeting $rtmMeeting;
    private AmiCycle $amiCycle;

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
            'nama_prodi' => 'Informatika',
            'kode_prodi' => 'IF-001',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $this->user->assignRole('super_admin');

        $body = AccreditationBody::query()->create([
            'code' => 'LAM-INFOKOM',
            'name' => 'LAM-INFOKOM',
            'kind' => 'LAM-INFOKOM',
            'status' => 'active',
        ]);
        $family = InstrumentFamily::query()->create([
            'accreditation_body_id' => $body->id,
            'code' => 'LAM-INFOKOM-APS',
            'name' => 'Instrumen APS LAM-INFOKOM',
            'scope_type' => 'program_study',
        ]);
        $version = InstrumentVersion::query()->create([
            'instrument_family_id' => $family->id,
            'version_label' => 'v2.1',
            'status' => 'active',
        ]);

        $this->accreditation = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'instrument_version_id' => $version->id,
            'code' => 'AKR-TEST-DOC',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Program Studi Informatika',
            'status' => 'in_progress',
            'owner_id' => $this->user->id,
        ]);

        $node = InstrumentNode::query()->create([
            'instrument_version_id' => $version->id,
            'node_type' => 'element',
            'code' => 'NODE-C1',
            'title' => 'Visi Keilmuan & Strategi',
            'requirement' => 'Kesesuaian visi dengan VMTS',
            'guidance' => 'Jelaskan tahapan pencapaian',
            'sort_order' => 1,
        ]);

        $section = AccreditationSection::query()->create([
            'accreditation_id' => $this->accreditation->id,
            'instrument_node_id' => $node->id,
            'code' => 'C.1',
            'title' => 'Kriteria 1 - Visi Misi',
            'section_type' => 'led',
            'sort_order' => 1,
        ]);

        $response = AccreditationResponse::query()->create([
            'accreditation_id' => $this->accreditation->id,
            'accreditation_section_id' => $section->id,
            'instrument_node_id' => $node->id,
            'response_key' => 'C.1-01',
            'response_type' => 'text',
            'response_text' => 'Narasi evaluasi diri tentang visi keilmuan prodi yang telah disosialisasikan secara terstruktur.',
            'status' => AccreditationResponse::STATUS_APPROVED,
            'last_edited_by' => $this->user->id,
        ]);

        $evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $this->user->id,
            'code' => 'EVD-RENSTRA',
            'title' => 'Rencana Strategis 2024-2029',
            'status' => 'verified',
        ]);

        EvidenceLink::query()->create([
            'evidence_id' => $evidence->id,
            'linkable_type' => AccreditationResponse::class,
            'linkable_id' => $response->id,
            'relation_type' => 'supporting',
            'citation_page' => 12,
            'citation_note' => 'Lihat Bab II Halaman 12',
            'is_required' => true,
        ]);

        $template = LkpsTemplate::query()->create([
            'instrument_version_id' => $version->id,
            'code' => 'T-DOSEN',
            'name' => 'Tabel Profil Dosen Tetap',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        LkpsTemplateColumn::query()->create([
            'lkps_template_id' => $template->id,
            'column_key' => 'nama_dosen',
            'label' => 'Nama Dosen',
            'data_type' => 'string',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        // Setup RTM Meeting
        $this->rtmMeeting = RtmMeeting::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'chair_id' => $this->user->id,
            'code' => 'RTM-2026-01',
            'title' => 'Rapat Tinjauan Manajemen Tindak Lanjut AMI',
            'held_at' => now(),
            'minutes' => 'Pembahasan evaluasi hasil audit dan penetapan target perbaikan.',
            'status' => 'completed',
        ]);

        RtmParticipant::query()->create([
            'rtm_meeting_id' => $this->rtmMeeting->id,
            'user_id' => $this->user->id,
            'role' => 'Ketua RTM / Kaprodi',
            'attended' => true,
        ]);

        RtmDecision::query()->create([
            'rtm_meeting_id' => $this->rtmMeeting->id,
            'code' => 'DEC-01',
            'decision' => 'Penyesuaian kurikulum berbasis OBE dan penambahan sarana lab.',
            'rationale' => 'Rekomendasi auditor AMI dan masukan dari alumni.',
            'status' => 'open',
        ]);

        // Setup AMI Cycle
        $this->amiCycle = AmiCycle::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'code' => 'AMI-2026-GANJIL',
            'name' => 'Audit Mutu Internal Semester Ganjil 2026',
            'period_year' => 2026,
            'status' => 'completed',
        ]);

        AmiFinding::query()->create([
            'ami_cycle_id' => $this->amiCycle->id,
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'reporter_id' => $this->user->id,
            'code' => 'FND-01',
            'classification' => 'nonconformity',
            'severity' => 'major',
            'condition' => 'RPS belum memuat pengukuran CPL spesifik per pertemuan.',
            'cause' => 'Sosialisasi format RPS OBE belum menyeluruh kepada dosen luar biasa.',
            'recommendation' => 'Adakan lokakarya penyusunan RPS OBE terpadu.',
            'status' => 'open',
        ]);
    }

    public function test_export_led_docx_and_html(): void
    {
        $this->actingAs($this->user);

        // Test Word .docx download
        $responseDocx = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'led-docx',
        ]));
        $responseDocx->assertOk();
        $responseDocx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Test HTML preview
        $responseHtml = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'led-html',
        ]));
        $responseHtml->assertOk();
        $responseHtml->assertSee('LAPORAN EVALUASI DIRI (LED)');
        $responseHtml->assertSee('Institut Teknologi Uji');
        $responseHtml->assertSee('Informatika');
        $responseHtml->assertSee('EVD-RENSTRA');
    }

    public function test_export_lkps_xlsx_and_html(): void
    {
        $this->actingAs($this->user);

        // Test Excel .xlsx download
        $responseXlsx = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'lkps-xlsx',
        ]));
        $responseXlsx->assertOk();
        $responseXlsx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Test LKPS HTML preview
        $responseHtml = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'lkps-html',
        ]));
        $responseHtml->assertOk();
        $responseHtml->assertSee('LAPORAN KINERJA (LKPS / LKPT)');
        $responseHtml->assertSee('T-DOSEN');
    }

    public function test_export_score_simulation_and_evidence_matrix(): void
    {
        $this->actingAs($this->user);

        // Test Score Simulation HTML
        $responseScore = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'score-simulation',
        ]));
        $responseScore->assertOk();
        $responseScore->assertSee('MATRIKS SIMULASI SKOR AKREDITASI');
        $responseScore->assertSee('Estimasi Skor Akhir');
        $responseScore->assertSee('Prediksi Peringkat');

        // Test Evidence Matrix HTML
        $responseMatrix = $this->get(route('accreditations.export', [
            'accreditation' => $this->accreditation->id,
            'type' => 'evidence-matrix-html',
        ]));
        $responseMatrix->assertOk();
        $responseMatrix->assertSee('PETA KESIAPAN BUKTI PENDUKUNG');
        $responseMatrix->assertSee('EVD-RENSTRA');
    }

    public function test_export_rtm_minutes_and_ami_summary(): void
    {
        $this->actingAs($this->user);

        // Test RTM Minutes HTML
        $responseRtm = $this->get(route('rtm-meetings.export-minutes', [
            'meeting' => $this->rtmMeeting->id,
        ]));
        $responseRtm->assertOk();
        $responseRtm->assertSee('RISALAH RAPAT TINJAUAN MANAJEMEN');
        $responseRtm->assertSee('DEC-01');
        $responseRtm->assertSee('RTM-2026-01');

        // Test AMI Summary HTML
        $responseAmi = $this->get(route('ami-cycles.export-summary', [
            'cycle' => $this->amiCycle->id,
        ]));
        $responseAmi->assertOk();
        $responseAmi->assertSee('LAPORAN HASIL AUDIT MUTU INTERNAL');
        $responseAmi->assertSee('FND-01');
        $responseAmi->assertSee('KTS Mayor');
    }
}
