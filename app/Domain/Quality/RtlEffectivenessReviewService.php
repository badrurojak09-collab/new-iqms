<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Domain\Integration\LinkEvidenceToRecord;
use App\Models\AuditLog;
use App\Models\Evidence;
use App\Models\RtlAction;
use App\Models\RtlEffectivenessReview;
use App\Models\SpmiEvaluation;
use App\Models\SpmiImprovementProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RtlEffectivenessReviewService
{
    public function review(RtlAction $action, User $actor, array $data): RtlEffectivenessReview
    {
        return $this->createDraft($action, $actor, $data);
    }

    public function createDraft(RtlAction $action, User $actor, array $data): RtlEffectivenessReview
    {
        abort_unless($actor->can('review rtl effectiveness'), 403, 'Anda tidak memiliki hak membuat review efektivitas RTL.');
        $this->guardReviewableAction($action);

        return DB::transaction(function () use ($action, $actor, $data): RtlEffectivenessReview {
            $evaluation = $this->resolveEvaluation($action, $data['spmi_evaluation_id'] ?? null);
            $review = RtlEffectivenessReview::create([
                'rtl_action_id' => $action->getKey(),
                'spmi_evaluation_id' => $evaluation?->getKey(),
                'reviewed_by' => $actor->getKey(),
                'outcome' => $data['outcome'],
                'effectiveness_score' => $data['effectiveness_score'] ?? null,
                'observed_result' => $data['observed_result'] ?? null,
                'evidence_summary' => $data['evidence_summary'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'ppepp_stage' => $data['ppepp_stage'] ?? 'evaluation',
                'follow_up_required' => (bool) ($data['follow_up_required'] ?? false),
                'status' => 'draft',
                'reviewed_at' => null,
            ]);
            $this->audit($actor, $review, 'rtl.effectiveness_review.created', ['rtl_action_id' => $action->getKey(), 'status' => 'draft']);

            return $review->refresh();
        });
    }

    public function attachOutcomeEvidence(RtlEffectivenessReview $review, User $actor, int $evidenceId, ?string $label = null): void
    {
        abort_unless($actor->can('review rtl effectiveness'), 403, 'Anda tidak memiliki hak menambahkan evidence review.');
        if ($review->status === 'approved') {
            throw new InvalidArgumentException('Review yang sudah approved tidak dapat diubah.');
        }
        $review->loadMissing('rtlAction');
        $evidence = Evidence::query()->whereKey($evidenceId)->firstOrFail();
        DB::transaction(function () use ($review, $actor, $evidence, $label): void {
            app(LinkEvidenceToRecord::class)->handle($evidence, $review, $actor->getKey(), $label ?? 'Evidence outcome effectiveness review');
            $this->audit($actor, $review, 'rtl.effectiveness_review.evidence_attached', ['evidence_id' => $evidence->getKey(), 'status' => $review->status]);
        });
    }

    public function submit(RtlEffectivenessReview $review, User $actor): RtlEffectivenessReview
    {
        abort_unless($actor->can('submit rtl effectiveness'), 403, 'Anda tidak memiliki hak submit effectiveness review.');
        $review->loadMissing('evidenceLinks.evidence', 'rtlAction');
        if ($review->status !== 'draft') {
            throw new InvalidArgumentException('Hanya review berstatus draft yang dapat disubmit.');
        }
        $this->guardEvidenceOutcome($review);
        $from = $review->status;
        $review->update(['status' => 'submitted', 'reviewed_at' => now()]);
        $this->audit($actor, $review, 'rtl.effectiveness_review.submitted', ['from' => $from, 'to' => 'submitted']);

        return $review->refresh();
    }

    public function approve(RtlEffectivenessReview $review, User $actor): RtlEffectivenessReview
    {
        abort_unless($actor->can('approve rtl effectiveness'), 403, 'Anda tidak memiliki hak approve effectiveness review.');
        $review->loadMissing('evidenceLinks.evidence', 'rtlAction');
        if ($review->status !== 'submitted') {
            throw new InvalidArgumentException('Hanya review berstatus submitted yang dapat di-approve.');
        }
        $this->guardEvidenceOutcome($review);

        return DB::transaction(function () use ($review, $actor): RtlEffectivenessReview {
            $review->update(['status' => 'approved', 'reviewed_at' => now()]);
            if ($review->follow_up_required && $review->spmi_evaluation_id && ! $review->improvementPrograms()->exists()) {
                $action = $review->rtlAction;
                SpmiImprovementProgram::create([
                    'spmi_evaluation_id' => $review->spmi_evaluation_id,
                    'effectiveness_review_id' => $review->getKey(),
                    'perguruan_tinggi_id' => $action->perguruan_tinggi_id,
                    'program_studi_id' => $action->program_studi_id,
                    'code' => 'PPEPP-RTL-'.$action->getKey().'-'.$review->getKey(),
                    'title' => 'Peningkatan dari efektivitas RTL: '.$action->title,
                    'action_plan' => $review->recommendation ?: 'Tindak lanjut peningkatan berdasarkan review efektivitas RTL.',
                    'owner_id' => $action->owner_id ?: $actor->getKey(),
                    'due_date' => $action->due_date,
                    'status' => 'planned',
                ]);
            }
            $this->audit($actor, $review, 'rtl.effectiveness_review.approved', ['from' => 'submitted', 'to' => 'approved', 'follow_up_required' => $review->follow_up_required]);

            return $review->refresh();
        });
    }

    private function guardReviewableAction(RtlAction $action): void
    {
        $action->loadMissing('evidenceLinks.evidence');
        if (! in_array($action->status, ['verified', 'closed'], true)) {
            throw new InvalidArgumentException('Effectiveness review hanya dapat dibuat pada RTL verified atau closed.');
        }
        if (! $action->evidenceLinks->contains(fn ($link): bool => $link->evidence?->status === 'verified')) {
            throw new InvalidArgumentException('Effectiveness review membutuhkan evidence completion RTL yang verified.');
        }
    }

    private function guardEvidenceOutcome(RtlEffectivenessReview $review): void
    {
        if (! $review->evidenceLinks->contains(fn ($link): bool => $link->evidence?->status === 'verified')) {
            throw new InvalidArgumentException('Review membutuhkan minimal satu evidence outcome yang verified.');
        }
    }

    private function resolveEvaluation(RtlAction $action, mixed $evaluationId): ?SpmiEvaluation
    {
        return $evaluationId ? SpmiEvaluation::query()->whereKey($evaluationId)->where('perguruan_tinggi_id', $action->perguruan_tinggi_id)->firstOrFail() : null;
    }

    private function audit(User $actor, object $record, string $event, array $context): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $record::class, 'auditable_id' => $record->getKey(), 'route' => request()->route()?->getName(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'context' => $context]);
    }
}
