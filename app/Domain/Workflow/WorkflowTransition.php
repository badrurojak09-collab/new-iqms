<?php

declare(strict_types=1);

namespace App\Domain\Workflow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class WorkflowTransition
{
    /** @var array<string, array<string, list<string>>> */
    private const MAP = [
        'ami_cycle' => ['draft' => ['in_progress'], 'in_progress' => ['submitted', 'completed'], 'submitted' => ['reviewed'], 'reviewed' => ['completed'], 'completed' => ['closed']],
        'accreditation' => ['readiness' => ['draft'], 'draft' => ['review'], 'review' => ['ready'], 'ready' => ['submitted'], 'submitted' => ['assessment'], 'assessment' => ['decision'], 'decision' => ['closed']],
        'ami_finding' => ['open' => ['accepted', 'in_progress'], 'accepted' => ['in_progress', 'closed'], 'in_progress' => ['verified', 'open'], 'verified' => ['closed']],
        'rtl_action' => ['open' => ['in_progress', 'blocked'], 'blocked' => ['open'], 'in_progress' => ['submitted', 'blocked'], 'submitted' => ['verified', 'in_progress'], 'verified' => ['closed']],
        'rtm_meeting' => ['planned' => ['completed', 'cancelled']],
    ];

    public function handle(Model $model, string $aggregate, string $to): Model
    {
        $from = (string) $model->getAttribute('status');
        if (! in_array($to, self::MAP[$aggregate][$from] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "Transition {$aggregate}:{$from} → {$to} tidak diizinkan."]);
        }

        $model->forceFill(['status' => $to])->save();

        return $model->refresh();
    }
}
