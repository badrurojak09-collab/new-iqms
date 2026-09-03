<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\LkeLedWorkspace;
use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\InstrumentFamily;
use App\Models\InstrumentNode;
use App\Models\InstrumentVersion;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AccreditationResponseWorkflowWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Accreditation $accreditation;
    private AccreditationResponse $response;
    private Evidence $evidence;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $yayasan = Yayasan::query()->create(['kode' => 'YYS-01', 'nama' => 'Yayasan Uji']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Institut Uji Mutu',
            'kode_pt' => 'IUM-001',
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
        $this->user->assignRole('super_admin');

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

        $this->accreditation = Accreditation::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'program_studi_id' => $prodi->id,
            'instrument_version_id' => $version->id,
            'code' => 'AKR-TEST-WF',
            'scope_type' => 'program_study',
            'title' => 'Akreditasi Workflow Test',
            'status' => 'in_progress',
            'owner_id' => $this->user->id,
        ]);

        $node = InstrumentNode::query()->create([
            'instrument_version_id' => $version->id,
            'node_type' => 'element',
            'code' => 'NODE-01',
            'title' => 'Elemen Budaya Mutu',
            'requirement' => 'Jelaskan kebijakan SPMI',
            'guidance' => 'Sertakan notula RTM',
            'sort_order' => 1,
        ]);

        $section = AccreditationSection::query()->create([
            'accreditation_id' => $this->accreditation->id,
            'instrument_node_id' => $node->id,
            'code' => 'SEC-01',
            'title' => 'Kriteria 1 - Visi Misi',
            'section_type' => 'led',
            'sort_order' => 1,
        ]);

        $this->response = AccreditationResponse::query()->create([
            'accreditation_id' => $this->accreditation->id,
            'accreditation_section_id' => $section->id,
            'instrument_node_id' => $node->id,
            'response_key' => 'LED-KR-01',
            'response_type' => 'text',
            'response_text' => 'Narasi awal evaluasi diri kriteria 1.',
            'status' => AccreditationResponse::STATUS_DRAFT,
            'last_edited_by' => $this->user->id,
        ]);

        $this->evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $this->user->id,
            'code' => 'EVD-001',
            'title' => 'Dokumen Renstra & SPMI',
            'status' => 'verified',
        ]);
    }

    public function test_response_workflow_submit_review_revision_and_approval(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(LkeLedWorkspace::class, ['accreditation' => $this->accreditation->id])
            ->assertSuccessful();

        // 1. Submit response
        $component->call('submitResponse', $this->response->id);
        $this->assertSame(AccreditationResponse::STATUS_SUBMITTED, $this->response->refresh()->status);

        // 2. Start review
        $component->call('startReviewResponse', $this->response->id);
        $this->assertSame(AccreditationResponse::STATUS_IN_REVIEW, $this->response->refresh()->status);

        // 3. Request revision
        $component->set('revisionResponseId', $this->response->id)
            ->set('revisionNotes', 'Mohon lengkapi bukti tabel pada poin 2')
            ->call('submitRevisionRequest');

        $this->assertSame(AccreditationResponse::STATUS_REVISION_REQUIRED, $this->response->refresh()->status);
        $this->assertSame('Mohon lengkapi bukti tabel pada poin 2', $this->response->refresh()->review_notes);

        // 4. Revise narrative
        $component->set('editingResponseId', $this->response->id)
            ->set('editingResponseText', 'Narasi yang telah diperbaiki dengan data lengkap.')
            ->call('saveResponseNarrative');

        $this->assertSame(AccreditationResponse::STATUS_DRAFT, $this->response->refresh()->status);
        $this->assertSame('Narasi yang telah diperbaiki dengan data lengkap.', $this->response->refresh()->response_text);
        $this->assertGreaterThanOrEqual(1, $this->response->revisions()->count());

        // 5. Submit and review again, then approve
        $component->call('submitResponse', $this->response->id);
        $component->call('startReviewResponse', $this->response->id);
        $component->call('approveResponse', $this->response->id);
        $this->assertSame(AccreditationResponse::STATUS_APPROVED, $this->response->refresh()->status);

        // 6. Lock response
        $component->call('lockResponse', $this->response->id);
        $this->assertSame(AccreditationResponse::STATUS_LOCKED, $this->response->refresh()->status);
        $this->assertTrue($this->response->refresh()->isLocked());
    }

    public function test_evidence_citation_link_and_detach(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(LkeLedWorkspace::class, ['accreditation' => $this->accreditation->id])
            ->assertSuccessful();

        // Open evidence link modal and attach evidence
        $component->set('evidenceModalResponseId', $this->response->id)
            ->set('selectedEvidenceId', $this->evidence->id)
            ->set('citationPage', 15)
            ->set('citationNote', 'Lihat tabel 2.3 capaian indikator')
            ->set('citationIsRequired', true)
            ->call('attachEvidenceLink');

        $link = EvidenceLink::query()
            ->where('linkable_type', AccreditationResponse::class)
            ->where('linkable_id', $this->response->id)
            ->first();

        $this->assertNotNull($link);
        $this->assertSame(15, $link->citation_page);
        $this->assertSame('Lihat tabel 2.3 capaian indikator', $link->citation_note);

        // Detach evidence
        $component->call('detachEvidenceLink', $link->id);
        $this->assertDatabaseMissing('evidence_links', ['id' => $link->id]);
    }
}
