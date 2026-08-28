<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\User;
use Spatie\Permission\Models\Role;

final class ScopedRoleManager
{
    public function assign(User $user, Role|string $role, string $scopeType, int $scopeId): void
    {
        $roleModel = $role instanceof Role
            ? $role
            : Role::query()->where('name', $role)->where('guard_name', 'web')->firstOrFail();

        $user->roles()->syncWithoutDetaching([$roleModel->getKey()]);
        $user->tenantScopes()->updateOrCreate(
            [
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'role_id' => $roleModel->getKey(),
            ],
            ['is_default' => false],
        );
    }

    public function has(User $user, string $role, string $scopeType, int $scopeId): bool
    {
        return $user->tenantScopes()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->whereHas('role', fn ($query) => $query->where('name', $role)->where('guard_name', 'web'))
            ->exists();
    }
}
