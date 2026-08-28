<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AmiCycle;
use App\Support\Audit\AuditLogger;

final class AmiCycleObserver
{
    public function created(AmiCycle $cycle): void
    {
        app(AuditLogger::class)->record('ami.cycle.created', $cycle, [], $cycle->getAttributes());
    }

    public function updated(AmiCycle $cycle): void
    {
        app(AuditLogger::class)->record('ami.cycle.updated', $cycle, $cycle->getOriginal(), $cycle->getChanges());
    }

    public function deleted(AmiCycle $cycle): void
    {
        app(AuditLogger::class)->record('ami.cycle.deleted', $cycle, $cycle->getAttributes(), []);
    }
}
