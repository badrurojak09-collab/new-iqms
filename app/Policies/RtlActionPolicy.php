<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RtlAction;
use App\Models\User;

class RtlActionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view ami') || $user->can('manage rtl');
    }

    public function view(User $user, RtlAction $action): bool
    {
        return $user->can('view ami') || $user->can('manage rtl');
    }

    public function create(User $user): bool
    {
        return $user->can('manage rtl');
    }

    public function update(User $user, RtlAction $action): bool
    {
        return $user->can('manage rtl') && ! in_array($action->status, ['verified', 'closed'], true);
    }

    public function delete(User $user, RtlAction $action): bool
    {
        return $user->can('manage rtl') && ! in_array($action->status, ['verified', 'closed'], true);
    }

    public function transition(User $user, RtlAction $action, string $toStatus): bool
    {
        return match ($toStatus) {
            'verified' => $user->can('verify rtl'), 'closed' => $user->can('close rtl'), default => $user->can('manage rtl')
        };
    }
}
