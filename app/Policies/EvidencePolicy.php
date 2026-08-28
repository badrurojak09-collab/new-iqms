<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Evidence;
use App\Models\User;

final class EvidencePolicy
{
    public function view(User $user, Evidence $evidence): bool
    {
        return $user->isSuperAdmin()
            || ($user->canAccessPerguruanTinggi($evidence->perguruanTinggi)
                && ($evidence->program_studi_id === null || $user->canAccessProgramStudi($evidence->programStudi)));
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage evidence');
    }

    public function update(User $user, Evidence $evidence): bool
    {
        return $this->view($user, $evidence)
            && ($user->isSuperAdmin() || $user->can('manage evidence'));
    }

    public function review(User $user, Evidence $evidence): bool
    {
        return $this->view($user, $evidence)
            && ($user->isSuperAdmin() || $user->can('review evidence'));
    }
}
