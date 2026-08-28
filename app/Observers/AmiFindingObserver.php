<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AmiFinding;
use App\Support\Audit\AuditLogger;

final class AmiFindingObserver
{
    public function created(AmiFinding $finding): void
    {
        app(AuditLogger::class)->record('ami.finding.created', $finding, [], $finding->getAttributes());
    }

    public function updated(AmiFinding $finding): void
    {
        app(AuditLogger::class)->record('ami.finding.updated', $finding, $finding->getOriginal(), $finding->getChanges());
    }

    public function deleted(AmiFinding $finding): void
    {
        app(AuditLogger::class)->record('ami.finding.deleted', $finding, $finding->getAttributes(), []);
    }
}
