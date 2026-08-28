<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Jobs\ReevaluateAccreditationReadiness;
use App\Models\AuditLog;
use App\Models\SpmiImprovementProgram;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class SpmiImprovementProgramLifecycleService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'planned' => ['in_progress'],
        'in_progress' => ['completed'],
        'completed' => ['verified'],
        'verified' => [],
    ];

    public function transition(SpmiImprovementProgram $program, User $actor, string $toStatus, ?string $notes = null): SpmiImprovementProgram
    {
        $from = (string) $program->status;
        if (! in_array($toStatus, self::TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidArgumentException("Transition SPMI improvement {$from} → {$toStatus} tidak diizinkan.");
        }
        abort_unless($actor->can($toStatus === 'verified' ? 'verify spmi improvement' : 'manage spmi'), 403, 'Anda tidak memiliki hak melakukan transition program peningkatan SPMI.');

        return DB::transaction(function () use ($program, $actor, $from, $toStatus, $notes): SpmiImprovementProgram {
            $values = ['status' => $toStatus];
            if ($notes !== null && in_array($toStatus, ['completed', 'verified'], true)) {
                $values['completion_notes'] = $notes;
            }
            if ($toStatus === 'verified') {
                $values['verified_by'] = $actor->getKey();
                $values['verified_at'] = now();
            }
            $program->update($values);
            $program->refresh();
            $this->audit($actor, $program, 'spmi.improvement.lifecycle.transitioned', ['from' => $from, 'to' => $toStatus, 'notes' => $notes]);

            if ($toStatus === 'verified' && $program->accreditation_id !== null) {
                $program->update(['re_evaluation_status' => 'queued', 're_evaluation_requested_at' => now(), 're_evaluation_error' => null]);
                ReevaluateAccreditationReadiness::dispatch($program->getKey(), $actor->getKey())->afterCommit();
                $this->audit($actor, $program, 'spmi.improvement.readiness_re_evaluation_queued', ['accreditation_id' => $program->accreditation_id, 'queue' => true]);
                $program->refresh();
            }

            return $program;
        });
    }

    private function audit(User $actor, object $record, string $event, array $context): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $record::class, 'auditable_id' => $record->getKey(), 'route' => request()->route()?->getName(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'context' => $context]);
    }
}
