<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgramStudi;
use App\Models\User;

final class ProgramStudiPolicy
{
    public function view(User $user, ProgramStudi $record): bool
    {
        return $user->canAccessProgramStudi($record);
    }

    public function update(User $user, ProgramStudi $record): bool
    {
        return $user->canAccessProgramStudi($record)
            && ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, ProgramStudi $record): bool
    {
        return $this->update($user, $record);
    }
}
