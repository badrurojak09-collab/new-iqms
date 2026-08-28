<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accreditation\SubmitAccreditation;
use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\AccreditationReadinessItem;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class AccreditationAggregateTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_aggregate_supports_institution_and_program_study_scope(): void
    {
        [$user, $pt, $prodi, $version] = $this->context();
        $institution = Accreditation::query()->create(['perguruan_tinggi_id' => $pt->id, 'instrument_version_id' => $version->id, 'code' => 'INST-2026', 'scope_type' => 'institution', 'title' => 'Akreditasi Institusi']);
        $studyProgram = Accreditation::query()->create(['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'instrument_version_id' => $version->id, 'code' => 'PRODI-2026', 'scope_type' => 'study_program', 'title' => 'Akreditasi Prodi']);

        self::assertNull($institution->program_studi_id);
        self::assertSame($prodi->id, $studyProgram->program_studi_id);
    }

    public function test_submission_requires_ready_responses_and_published_instrument(): void
    {
        [$user, $pt, $prodi, $version] = $this->context();
        $accreditation = Accreditation::query()->create(['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'instrument_version_id' => $version->id, 'code' => 'PRODI-SUB', 'scope_type' => 'study_program', 'title' => 'Submission Prodi']);
        $section = AccreditationSection::query()->create(['accreditation_id' => $accreditation->id, 'instrument_node_id' => $version->nodes()->first()->id, 'code' => 'LED-A', 'title' => 'Tata Kelola']);
        AccreditationResponse::query()->create(['accreditation_id' => $accreditation->id, 'accreditation_section_id' => $section->id, 'instrument_node_id' => $version->nodes()->first()->id, 'response_key' => 'LED.A.1', 'response_type' => 'text', 'response_text' => 'Narasi terverifikasi', 'status' => 'ready', 'last_edited_by' => $user->id]);
        AccreditationReadinessItem::query()->create(['accreditation_id' => $accreditation->id, 'item_type' => 'evidence', 'item_key' => 'LED-A-1', 'status' => 'complete', 'checked_by' => $user->id, 'checked_at' => now()]);

        $submission = app(SubmitAccreditation::class)->handle($accreditation, $user->id);

        self::assertSame(1, $submission->submission_no);
        self::assertSame('submitted', $submission->status);
        self::assertNotNull($submission->package_hash);
        self::assertSame('submitted', $accreditation->refresh()->status);
    }

    public function test_submission_rejects_draft_instrument(): void
    {
        [$user, $pt, $prodi, $version] = $this->context();
        $draftVersion = $version->family->versions()->create(['version_label' => '2.2', 'status' => 'draft']);
        $accreditation = Accreditation::query()->create(['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'instrument_version_id' => $draftVersion->id, 'code' => 'PRODI-DRAFT', 'scope_type' => 'study_program', 'title' => 'Draft Instrument']);

        $this->expectException(ValidationException::class);
        app(SubmitAccreditation::class)->handle($accreditation, $user->id);
    }

    /** @return array{0: User, 1: PerguruanTinggi, 2: ProgramStudi, 3: InstrumentVersion} */
    private function context(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Akreditasi', 'kode' => uniqid('YK')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Akreditasi', 'kode_pt' => uniqid('PTA')]);
        $prodi = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi Akreditasi', 'kode_prodi' => uniqid('PRA')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);
        $body = AccreditationBody::query()->create(['code' => 'LAM-TEST', 'name' => 'LAM Test']);
        $family = InstrumentFamily::query()->create(['accreditation_body_id' => $body->id, 'code' => 'IAPS-TEST', 'name' => 'IAPS Test', 'scope_type' => 'study_program']);
        $version = $family->versions()->create(['version_label' => '2.1', 'status' => 'published', 'content_hash' => hash('sha256', 'fixture'), 'published_at' => now(), 'published_by' => $user->id]);
        $version->nodes()->create(['node_type' => 'criterion', 'code' => 'A', 'title' => 'Governance', 'sort_order' => 1]);

        return [$user, $pt, $prodi, $version->fresh('nodes')];
    }
}
