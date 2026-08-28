<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserTenantScope;

final class UserTenantScopePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, UserTenantScope $scope): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, UserTenantScope $scope): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, UserTenantScope $scope): bool
    {
        return $user->isSuperAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
