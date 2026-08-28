<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RtmMeeting;
use App\Models\User;

final class RtmMeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage rtm') || $user->can('view ami');
    }

    public function view(User $user, RtmMeeting $meeting): bool
    {
        return ($user->can('manage rtm') || $user->can('view ami'))
            && $user->canAccessPerguruanTinggi($meeting->perguruanTinggi);
    }

    public function create(User $user): bool
    {
        return $user->can('manage rtm');
    }

    public function update(User $user, RtmMeeting $meeting): bool
    {
        return $user->can('manage rtm')
            && $user->canAccessPerguruanTinggi($meeting->perguruanTinggi)
            && $meeting->status !== 'completed';
    }

    public function delete(User $user, RtmMeeting $meeting): bool
    {
        return $user->can('manage rtm')
            && $user->canAccessPerguruanTinggi($meeting->perguruanTinggi)
            && $meeting->status === 'planned';
    }
}
