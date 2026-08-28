<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RtmDecision;
use App\Models\User;

final class RtmDecisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage rtm') || $user->can('view ami');
    }

    public function view(User $user, RtmDecision $decision): bool
    {
        return ($user->can('manage rtm') || $user->can('view ami'))
            && $user->canAccessPerguruanTinggi($decision->meeting->perguruanTinggi);
    }

    public function create(User $user): bool
    {
        return $user->can('manage rtm');
    }

    public function update(User $user, RtmDecision $decision): bool
    {
        return $user->can('manage rtm')
            && $user->canAccessPerguruanTinggi($decision->meeting->perguruanTinggi)
            && $decision->status !== 'closed';
    }

    public function delete(User $user, RtmDecision $decision): bool
    {
        return $user->can('manage rtm')
            && $user->canAccessPerguruanTinggi($decision->meeting->perguruanTinggi)
            && $decision->status !== 'closed';
    }
}
