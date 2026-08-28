<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AmiCycle;
use App\Models\User;

final class AmiCyclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view ami');
    }

    public function view(User $user, AmiCycle $cycle): bool
    {
        return $user->can('view ami') && $user->canAccessPerguruanTinggi($cycle->perguruanTinggi);
    }

    public function create(User $user): bool
    {
        return $user->can('manage ami');
    }

    public function update(User $user, AmiCycle $cycle): bool
    {
        return $user->can('manage ami')
            && $user->canAccessPerguruanTinggi($cycle->perguruanTinggi)
            && $cycle->status !== 'closed';
    }

    public function delete(User $user, AmiCycle $cycle): bool
    {
        return $user->can('manage ami')
            && $user->canAccessPerguruanTinggi($cycle->perguruanTinggi)
            && $cycle->status === 'draft';
    }

    public function transition(User $user, AmiCycle $cycle, string $toStatus): bool
    {
        return $user->canAccessPerguruanTinggi($cycle->perguruanTinggi)
            && match ($toStatus) {
                'in_progress', 'completed', 'closed' => $user->can('manage ami') || ($toStatus === 'completed' && $user->can('review ami')),
                default => false,
            };
    }
}
