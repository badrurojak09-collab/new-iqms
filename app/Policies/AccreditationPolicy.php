<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Accreditation;
use App\Models\User;

final class AccreditationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view accreditation') || $user->can('manage accreditation');
    }

    public function view(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation)
            && ($user->isSuperAdmin() || $user->can('view accreditation') || $user->can('manage accreditation') || $user->can('review accreditation'));
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage accreditation');
    }

    public function update(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation)
            && ($user->isSuperAdmin() || $user->can('manage accreditation'))
            && ! in_array($accreditation->status, ['submitted', 'completed'], true);
    }

    public function delete(User $user, Accreditation $accreditation): bool
    {
        return $this->update($user, $accreditation);
    }

    public function review(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation)
            && ($user->isSuperAdmin() || $user->can('review accreditation'));
    }

    public function approve(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation)
            && ($user->isSuperAdmin() || $user->can('approve accreditation'));
    }

    private function hasTenantAccess(User $user, Accreditation $accreditation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->canAccessPerguruanTinggi($accreditation->perguruanTinggi)) {
            return false;
        }

        return $accreditation->program_studi_id === null
            || $user->canAccessProgramStudi($accreditation->programStudi);
    }
}
