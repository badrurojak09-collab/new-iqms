<?php

declare(strict_types=1);

namespace App\Policies;

final class RolePolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view security';
    }

    protected function managePermission(): ?string
    {
        return 'manage security';
    }

    protected function readOnly(): bool
    {
        return false;
    }
}
