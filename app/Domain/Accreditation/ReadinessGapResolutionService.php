<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Domain\Integration\LinkEvidenceToRecord;
use App\Models\AuditLog;
use App\Models\Evidence;
use App\Models\ReadinessGap;
use App\Models\RtlAction;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ReadinessGapResolutionService
{
    public function createRtl(ReadinessGap $gap, User $actor, array $data): RtlAction
    {
        return DB::transaction(function () use ($gap, $actor, $data): RtlAction {
            $accreditation = $gap->loadMissing('run.accreditation')->run->accreditation;
            $action = RtlAction::create([
                'perguruan_tinggi_id' => $accreditation->perguruan_tinggi_id,
                'program_studi_id' => $accreditation->program_studi_id,
                'readiness_gap_id' => $gap->getKey(),
                'owner_id' => $data['owner_id'] ?? $actor->getKey(),
                'code' => $data['code'] ?? 'RDG-RTL-'.$gap->getKey().'-'.now()->format('YmdHis'),
                'title' => $data['title'] ?? 'Tindak lanjut readiness gap '.$gap->item_key,
                'action_plan' => $data['action_plan'] ?? $gap->description,
                'due_date' => $data['due_date'] ?? null,
                'status' => 'open',
            ]);
            $gap->update(['resolution_status' => 'in_progress']);
            $this->audit($actor, $action, 'readiness_gap.rtl_created', ['readiness_gap_id' => $gap->getKey()]);

            return $action;
        });
    }

    public function createRtmDecision(ReadinessGap $gap, User $actor, int $meetingId, array $data): RtmDecision
    {
        return DB::transaction(function () use ($gap, $actor, $meetingId, $data): RtmDecision {
            $accreditation = $gap->loadMissing('run.accreditation')->run->accreditation;
            $meeting = RtmMeeting::query()->whereKey($meetingId)->where('perguruan_tinggi_id', $accreditation->perguruan_tinggi_id)->firstOrFail();
            $decision = RtmDecision::create([
                'rtm_meeting_id' => $meeting->getKey(),
                'readiness_gap_id' => $gap->getKey(),
                'code' => $data['code'] ?? 'RDG-RTM-'.$gap->getKey().'-'.now()->format('YmdHis'),
                'decision' => $data['decision'] ?? 'Menyelesaikan gap readiness: '.$gap->description,
                'rationale' => $data['rationale'] ?? null,
                'status' => 'approved',
            ]);
            $gap->update(['resolution_status' => 'in_progress']);
            $this->audit($actor, $decision, 'readiness_gap.rtm_decision_created', ['readiness_gap_id' => $gap->getKey(), 'rtm_meeting_id' => $meeting->getKey()]);

            return $decision;
        });
    }

    public function attachCompletionEvidence(ReadinessGap $gap, User $actor, int $rtlActionId, int $evidenceId, ?string $label = null): void
    {
        DB::transaction(function () use ($gap, $actor, $rtlActionId, $evidenceId, $label): void {
            $action = $gap->rtlActions()->whereKey($rtlActionId)->firstOrFail();
            app(LinkEvidenceToRecord::class)->handle(Evidence::query()->whereKey($evidenceId)->firstOrFail(), $action, $actor->getKey(), $label ?? 'Evidence penyelesaian RTL');
            $this->audit($actor, $action, 'rtl.completion_evidence_attached', ['readiness_gap_id' => $gap->getKey(), 'evidence_id' => $evidenceId]);
        });
    }

    public function resolveGap(ReadinessGap $gap, User $actor): ReadinessGap
    {
        $gap->loadMissing('rtlActions.evidenceLinks.evidence');
        $verified = $gap->rtlActions->first(fn ($action): bool => $action->status === 'verified' && $action->evidenceLinks->contains(fn ($link): bool => $link->evidence?->status === 'verified'));
        if (! $verified) {
            throw new \InvalidArgumentException('Gap belum dapat di-resolve. Minimal satu RTL harus verified dan memiliki evidence completion verified.');
        }
        $resolved = $gap->resolve($actor, $verified);
        $this->audit($actor, $resolved, 'readiness_gap.resolved', ['rtl_action_id' => $verified->getKey()]);

        return $resolved;
    }

    private function audit(User $actor, object $record, string $event, array $context): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $record::class, 'auditable_id' => $record->getKey(), 'route' => request()->route()?->getName(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'context' => $context]);
    }
}
