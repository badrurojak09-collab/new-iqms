<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isSuperAdmin() && ! $target->isSuperAdmin();
    }

    public function impersonate(User $user, User $target): bool
    {
        return $user->isSuperAdmin() && ! $target->isSuperAdmin() && $user->getKey() !== $target->getKey();
    }
}
