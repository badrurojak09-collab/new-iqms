<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AmiFinding;
use App\Models\User;

final class AmiFindingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view ami');
    }

    public function view(User $user, AmiFinding $finding): bool
    {
        return $user->can('view ami') && $user->canAccessPerguruanTinggi($finding->cycle->perguruanTinggi);
    }

    public function create(User $user): bool
    {
        return $user->can('manage ami') || $user->can('review ami');
    }

    public function update(User $user, AmiFinding $finding): bool
    {
        return ($user->can('manage ami') || $user->can('review ami'))
            && $user->canAccessPerguruanTinggi($finding->cycle->perguruanTinggi)
            && $finding->status !== 'closed';
    }

    public function delete(User $user, AmiFinding $finding): bool
    {
        return $user->can('manage ami')
            && $user->canAccessPerguruanTinggi($finding->cycle->perguruanTinggi)
            && $finding->status === 'open';
    }
}
