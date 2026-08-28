<?php

declare(strict_types=1);

namespace App\Policies;

final class AuditLogPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view audit log';
    }

    protected function managePermission(): ?string
    {
        return null;
    }

    protected function readOnly(): bool
    {
        return true;
    }
}
