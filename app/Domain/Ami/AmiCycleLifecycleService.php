<?php

declare(strict_types=1);

namespace App\Domain\Ami;

use App\Models\AmiCycle;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AmiCycleLifecycleService
{
    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['in_progress'],
        'in_progress' => ['completed'],
        'completed' => ['closed'],
    ];

    public function start(AmiCycle $cycle, User $actor): AmiCycle
    {
        return $this->transition($cycle, $actor, 'in_progress');
    }

    public function complete(AmiCycle $cycle, User $actor): AmiCycle
    {
        return $this->transition($cycle, $actor, 'completed');
    }

    public function close(AmiCycle $cycle, User $actor): AmiCycle
    {
        return $this->transition($cycle, $actor, 'closed');
    }

    public function transition(AmiCycle $cycle, User $actor, string $toStatus): AmiCycle
    {
        $this->authorize($actor, $cycle, $toStatus);

        return DB::transaction(function () use ($cycle, $actor, $toStatus): AmiCycle {
            $fromStatus = (string) $cycle->status;

            if (! in_array($toStatus, self::TRANSITIONS[$fromStatus] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf('Perubahan status dari %s ke %s tidak diizinkan.', $fromStatus, $toStatus),
                ]);
            }

            if ($toStatus === 'in_progress' && $cycle->planned_start === null) {
                $cycle->planned_start = today();
            }

            if ($toStatus === 'completed') {
                $unfinished = $cycle->checklistItems()
                    ->whereNotIn('response_status', ['completed', 'verified'])
                    ->count();

                if ($unfinished > 0) {
                    throw ValidationException::withMessages([
                        'status' => sprintf('%d checklist AMI belum selesai atau diverifikasi.', $unfinished),
                    ]);
                }

                $cycle->actual_end = today();
            }

            if ($toStatus === 'in_progress' && $cycle->actual_start === null) {
                $cycle->actual_start = today();
            }

            $cycle->status = $toStatus;
            $cycle->save();

            app(AuditLogger::class)->record(
                'ami.cycle.status_changed',
                $cycle,
                ['status' => $fromStatus],
                ['status' => $toStatus],
                ['actor_id' => $actor->getKey(), 'ami_cycle_id' => $cycle->getKey()],
            );

            return $cycle->refresh();
        });
    }

    private function authorize(User $actor, AmiCycle $cycle, string $toStatus): void
    {
        if (! $actor->can('manage ami') && ! ($toStatus === 'completed' && $actor->can('review ami'))) {
            throw new AccessDeniedHttpException('Pengguna tidak memiliki hak untuk mengubah siklus AMI.');
        }

        if ($cycle->perguruan_tinggi_id !== null && ! $actor->canAccessPerguruanTinggi($cycle->perguruanTinggi)) {
            throw new AccessDeniedHttpException('Siklus AMI berada di luar tenant pengguna.');
        }
    }
}
