<?php

declare(strict_types=1);

namespace App\Policies;

final class SpmiStandardPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view spmi';
    }

    protected function managePermission(): ?string
    {
        return 'manage spmi';
    }

    protected function readOnly(): bool
    {
        return false;
    }
}
