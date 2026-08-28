<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\RtmMeeting;
use App\Support\Audit\AuditLogger;

final class RtmMeetingObserver
{
    public function created(RtmMeeting $meeting): void
    {
        app(AuditLogger::class)->record('rtm.meeting.created', $meeting, [], $meeting->getAttributes());
    }

    public function updated(RtmMeeting $meeting): void
    {
        app(AuditLogger::class)->record('rtm.meeting.updated', $meeting, $meeting->getOriginal(), $meeting->getChanges());
    }

    public function deleted(RtmMeeting $meeting): void
    {
        app(AuditLogger::class)->record('rtm.meeting.deleted', $meeting, $meeting->getAttributes(), []);
    }
}
