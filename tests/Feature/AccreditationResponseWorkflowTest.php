<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Accreditation\AccreditationResponseWorkflowService;
use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class AccreditationResponseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_can_move_through_review_revision_approval_and_lock(): void
    {
        [$user, $response] = $this->context();
        $this->actingAs($user);
        $service = app(AccreditationResponseWorkflowService::class);

        $service->submit($response, $user);
        self::assertSame(AccreditationResponse::STATUS_SUBMITTED, $response->refresh()->status);
        self::assertNotNull($response->submitted_at);

        $service->startReview($response, $user);
        self::assertSame(AccreditationResponse::STATUS_IN_REVIEW, $response->refresh()->status);

        $service->requestRevision($response, $user, 'Narasi perlu dilengkapi dengan sumber data.');
        self::assertSame(AccreditationResponse::STATUS_REVISION_REQUIRED, $response->refresh()->status);

        $service->revise($response, $user, ['response_text' => 'Narasi telah dilengkapi dengan sumber data.'], 'Perbaikan sesuai catatan reviewer.');
        self::assertSame(AccreditationResponse::STATUS_DRAFT, $response->refresh()->status);

        $service->submit($response, $user);
        $service->startReview($response, $user);
        $service->approve($response, $user);
        $service->lock($response, $user);

        $response->refresh();
        self::assertSame(AccreditationResponse::STATUS_LOCKED, $response->status);
        self::assertTrue($response->isLocked());
        self::assertGreaterThanOrEqual(5, $response->revisions()->count());
        self::assertNotNull($response->locked_at);
    }

    /** @return array{0: User, 1: AccreditationResponse} */
    private function context(): array
    {
        foreach (['manage accreditation', 'review accreditation', 'approve accreditation'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Response Test', 'kode' => uniqid('YRT')]);
        $pt = $yayasan->perguruanTinggis()->create(['nama_pt' => 'PT Response Test', 'kode_pt' => uniqid('PRT')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);
        $user->givePermissionTo(['manage accreditation', 'review accreditation', 'approve accreditation']);
        $body = AccreditationBody::query()->create(['code' => 'BODY-RESP', 'name' => 'Lembaga Response Test']);
        $family = InstrumentFamily::query()->create(['accreditation_body_id' => $body->id, 'code' => 'INST-RESP', 'name' => 'Instrumen Response Test', 'scope_type' => 'institution']);
        $version = $family->versions()->create(['version_label' => '1.0', 'status' => 'published', 'content_hash' => hash('sha256', 'response-test'), 'published_at' => now(), 'published_by' => $user->id]);
        $node = $version->nodes()->create(['node_type' => 'criterion', 'code' => 'R-01', 'title' => 'Kriteria Response', 'sort_order' => 1]);
        $accreditation = Accreditation::query()->create(['perguruan_tinggi_id' => $pt->id, 'instrument_version_id' => $version->id, 'code' => 'ACC-RESP', 'scope_type' => 'institution', 'title' => 'Akreditasi Response Test']);
        $section = AccreditationSection::query()->create(['accreditation_id' => $accreditation->id, 'instrument_node_id' => $node->id, 'code' => 'SEC-RESP', 'title' => 'Bagian Response']);
        $response = AccreditationResponse::query()->create(['accreditation_id' => $accreditation->id, 'accreditation_section_id' => $section->id, 'instrument_node_id' => $node->id, 'response_key' => 'LKE.RESP.01', 'response_type' => 'text', 'response_text' => 'Draf narasi.', 'status' => AccreditationResponse::STATUS_DRAFT, 'last_edited_by' => $user->id, 'revision_no' => 1]);

        return [$user, $response];
    }
}
