<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AccreditationScoreSnapshot;
use App\Models\User;

final class AccreditationScoreSnapshotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view accreditation') || $user->can('review accreditation');
    }

    public function view(User $user, AccreditationScoreSnapshot $snapshot): bool
    {
        $accreditation = $snapshot->loadMissing('accreditation')->accreditation;

        return $accreditation !== null
            && ($user->isSuperAdmin() || $user->can('view accreditation') || $user->can('review accreditation'))
            && $user->canAccessPerguruanTinggi($accreditation->perguruanTinggi)
            && ($accreditation->program_studi_id === null || $user->canAccessProgramStudi($accreditation->programStudi));
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AccreditationScoreSnapshot $snapshot): bool
    {
        return false;
    }

    public function delete(User $user, AccreditationScoreSnapshot $snapshot): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
