<?php

declare(strict_types=1);

namespace App\Domain\InstrumentRegistry;

use App\Models\AssessmentRubric;
use App\Models\AssessmentThreshold;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ApproveAssessmentConfiguration
{
    public function approveThreshold(AssessmentThreshold $threshold, User $actor, ?string $notes = null): AssessmentThreshold
    {
        $this->authorizeApproval($actor);

        return DB::transaction(function () use ($threshold, $actor, $notes): AssessmentThreshold {
            $this->assertDraftVersion($threshold->instrumentVersion);
            $threshold->update(['status' => 'approved', 'approved_by' => $actor->getKey(), 'approved_at' => now(), 'approval_notes' => $notes]);
            $this->audit($actor, $threshold, 'assessment_threshold.approved', $notes);

            return $threshold->refresh();
        });
    }

    public function approveRubric(AssessmentRubric $rubric, User $actor, ?string $notes = null): AssessmentRubric
    {
        $this->authorizeApproval($actor);

        return DB::transaction(function () use ($rubric, $actor, $notes): AssessmentRubric {
            $this->assertDraftVersion($rubric->instrumentVersion);
            $rubric->update(['status' => 'approved', 'approved_by' => $actor->getKey(), 'approved_at' => now(), 'approval_notes' => $notes]);
            $this->audit($actor, $rubric, 'assessment_rubric.approved', $notes);

            return $rubric->refresh();
        });
    }

    private function authorizeApproval(User $actor): void
    {
        abort_unless($actor->can('approve instrument configuration'), 403, 'Anda tidak memiliki hak untuk approve konfigurasi instrumen.');
    }

    private function assertDraftVersion($version): void
    {
        if (! $version || $version->status === 'published') {
            throw new InvalidArgumentException('Threshold atau rubric hanya dapat disetujui pada instrument version draft/review.');
        }
    }

    private function audit(User $actor, object $record, string $event, ?string $notes): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $record::class, 'auditable_id' => $record->getKey(), 'new_values' => ['status' => 'approved', 'notes' => $notes], 'route' => request()->route()?->getName(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'context' => ['approval_notes' => $notes]]);
    }
}
