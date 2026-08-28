<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\RtmDecision;
use App\Support\Audit\AuditLogger;

final class RtmDecisionObserver
{
    public function created(RtmDecision $decision): void
    {
        app(AuditLogger::class)->record('rtm.decision.created', $decision, [], $decision->getAttributes());
    }

    public function updated(RtmDecision $decision): void
    {
        app(AuditLogger::class)->record('rtm.decision.updated', $decision, $decision->getOriginal(), $decision->getChanges());
    }

    public function deleted(RtmDecision $decision): void
    {
        app(AuditLogger::class)->record('rtm.decision.deleted', $decision, $decision->getAttributes(), []);
    }
}
