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
        return $user->isSuperAdmin()
            && ($user->getKey() === $target->getKey() || ! $target->isSuperAdmin());
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isSuperAdmin()
            && ($user->getKey() === $target->getKey() || ! $target->isSuperAdmin());
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isSuperAdmin()
            && $user->getKey() !== $target->getKey()
            && ! $target->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function impersonate(User $user, User $target): bool
    {
        return $user->isSuperAdmin()
            && ! $target->isSuperAdmin()
            && $user->getKey() !== $target->getKey();
    }
}
