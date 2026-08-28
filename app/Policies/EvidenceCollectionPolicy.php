<?php

declare(strict_types=1);

namespace App\Policies;

final class EvidenceCollectionPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view evidence';
    }

    protected function managePermission(): ?string
    {
        return 'manage evidence';
    }

    protected function readOnly(): bool
    {
        return false;
    }
}
