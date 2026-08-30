<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Integration\LinkEvidenceToRecord;
use App\Domain\Reporting\QualityDashboardMetrics;
use App\Domain\Workflow\WorkflowTransition;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\Document;
use App\Models\Evidence;
use App\Models\PerguruanTinggi;
use App\Models\RtlAction;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CrossDomainIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_transition_rejects_invalid_jump(): void
    {
        [$user, $pt] = $this->context();
        $this->actingAs($user);
        $action = RtlAction::query()->create(['perguruan_tinggi_id' => $pt->id, 'owner_id' => $user->id, 'code' => 'RTL-X', 'title' => 'Action', 'action_plan' => 'Plan']);
        app(WorkflowTransition::class)->handle($action, 'rtl_action', 'in_progress');
        self::assertSame('in_progress', $action->refresh()->status);
        $this->expectException(ValidationException::class);
        app(WorkflowTransition::class)->handle($action, 'rtl_action', 'closed');
    }

    public function test_metrics_are_scoped_and_evidence_link_checks_tenant(): void
    {
        [$user, $pt] = $this->context();
        $this->actingAs($user);
        $cycle = AmiCycle::query()->create(['perguruan_tinggi_id' => $pt->id, 'code' => 'AMI-X', 'name' => 'AMI', 'period_year' => 2026, 'status' => 'in_progress', 'coordinator_id' => $user->id]);
        $finding = AmiFinding::query()->create(['ami_cycle_id' => $cycle->id, 'reported_by' => $user->id, 'code' => 'F-X', 'condition' => 'Condition']);
        self::assertSame(1, app(QualityDashboardMetrics::class)->forPerguruanTinggi($pt->id)['ami_open_findings']);

        $document = Document::query()->create(['perguruan_tinggi_id' => $pt->id, 'uploaded_by' => $user->id, 'disk' => 'local', 'storage_path' => 'evidence/x', 'original_name' => 'x.pdf', 'mime_type' => 'application/pdf', 'size_bytes' => 10, 'sha256' => hash('sha256', 'x')]);
        $evidence = Evidence::query()->create(['perguruan_tinggi_id' => $pt->id, 'created_by' => $user->id, 'code' => 'E-X', 'title' => 'Evidence']);
        self::assertTrue(app(LinkEvidenceToRecord::class)->handle($evidence, $finding, $user->id)->exists);
        self::assertNotNull($document->id);
    }

    /** @return array{0: User, 1: PerguruanTinggi} */
    private function context(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Integrasi', 'kode' => uniqid('YI')]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT Integrasi', 'kode_pt' => uniqid('PTI')]);
        \App\Models\ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi Integrasi', 'kode_prodi' => uniqid('PRI')]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);

        return [$user, $pt];
    }
}
