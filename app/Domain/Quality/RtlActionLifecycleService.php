<?php

declare(strict_types=1);

namespace App\Domain\Quality;

use App\Models\AuditLog;
use App\Models\RtlAction;
use App\Models\User;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class RtlActionLifecycleService
{
    private const TRANSITIONS = [
        'open' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => ['verified'],
        'verified' => ['closed'],
        'cancelled' => [],
        'closed' => [],
    ];

    public function transition(RtlAction $action, User $actor, string $toStatus, ?string $reason = null): RtlAction
    {
        $fromStatus = (string) $action->status;
        if (! in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
            throw new InvalidArgumentException("Transisi RTL {$fromStatus} ke {$toStatus} tidak diizinkan.");
        }
        $this->authorize($actor, $toStatus);
        $this->guardTenantIntegrity($action, $actor);

        return DB::transaction(function () use ($action, $actor, $fromStatus, $toStatus, $reason): RtlAction {
            if ($toStatus === 'verified' && ! $action->loadMissing('evidenceLinks.evidence')->evidenceLinks->contains(fn ($link): bool => $link->evidence?->status === 'verified')) {
                throw new InvalidArgumentException('RTL hanya dapat diverifikasi jika memiliki evidence completion berstatus verified.');
            }
            $values = ['status' => $toStatus];
            if ($reason !== null && in_array($toStatus, ['completed', 'cancelled'], true)) {
                $values['evidence_of_completion'] = $reason;
            }
            if ($toStatus === 'verified') {
                $values['verified_by'] = $actor->getKey();
                $values['verified_at'] = now();
            }
            $action->update($values);
            $action->refresh();
            $this->audit($actor, $action, 'rtl.lifecycle.transitioned', ['from' => $fromStatus, 'to' => $toStatus, 'reason' => $reason]);

            if ($toStatus === 'verified' && $action->readiness_gap_id !== null) {
                $gap = $action->loadMissing('readinessGap')->readinessGap;
                if ($gap !== null && $gap->resolution_status !== 'resolved') {
                    $gap->resolve($actor, $action);
                    $this->audit($actor, $gap, 'readiness_gap.auto_resolved_by_verified_rtl', ['rtl_action_id' => $action->getKey(), 'resolution_status' => 'resolved']);
                }
            }

            return $action;
        });
    }

    private function guardTenantIntegrity(RtlAction $action, User $actor): void
    {
        $action->loadMissing('finding.cycle', 'decision.meeting', 'readinessGap');
        $ptId = (int) $action->perguruan_tinggi_id;
        foreach ([$action->finding?->cycle?->perguruan_tinggi_id, $action->decision?->meeting?->perguruan_tinggi_id, $action->readinessGap?->perguruan_tinggi_id] as $relatedPtId) {
            if ($relatedPtId !== null && (int) $relatedPtId !== $ptId) {
                throw new InvalidArgumentException('Relasi RTL harus berada pada Perguruan Tinggi yang sama.');
            }
        }
        if (! TenantQuery::canAccessTenantRecord($actor, $action->perguruan_tinggi_id, $action->program_studi_id)) {
            throw new InvalidArgumentException('RTL berada di luar tenant pengguna.');
        }
    }

    private function authorize(User $actor, string $toStatus): void
    {
        $permission = match ($toStatus) {
            'in_progress', 'completed', 'cancelled' => 'manage rtl',
            'verified' => 'verify rtl',
            'closed' => 'close rtl',
            default => 'manage rtl',
        };
        abort_unless($actor->can($permission), 403, 'Anda tidak memiliki hak untuk melakukan transisi RTL ini.');
    }

    private function audit(User $actor, RtlAction $action, string $event, array $context): void
    {
        AuditLog::create(['user_id' => $actor->getKey(), 'event' => $event, 'auditable_type' => $action::class, 'auditable_id' => $action->getKey(), 'route' => request()->route()?->getName(), 'ip_address' => request()->ip(), 'user_agent' => request()->userAgent(), 'old_values' => ['status' => $context['from']], 'new_values' => ['status' => $context['to']], 'context' => $context]);
    }
}
