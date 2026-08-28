<?php

declare(strict_types=1);

namespace App\Policies;

final class ReadinessRunPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view accreditation';
    }

    protected function managePermission(): ?string
    {
        return 'review accreditation';
    }

    protected function readOnly(): bool
    {
        return true;
    }
}
