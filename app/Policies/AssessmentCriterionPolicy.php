<?php

declare(strict_types=1);

namespace App\Policies;

final class AssessmentCriterionPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view instrument configuration';
    }

    protected function managePermission(): ?string
    {
        return 'manage instrument configuration';
    }

    protected function readOnly(): bool
    {
        return false;
    }
}
