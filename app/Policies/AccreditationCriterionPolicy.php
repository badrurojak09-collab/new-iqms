<?php

declare(strict_types=1);

namespace App\Policies;

final class AccreditationCriterionPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view accreditation';
    }

    protected function managePermission(): ?string
    {
        return 'manage accreditation';
    }

    protected function readOnly(): bool
    {
        return false;
    }
}
