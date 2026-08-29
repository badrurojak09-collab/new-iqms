<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\ProgramStudi;
use App\Models\User;

final class ProgramStudiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() ||
            $user->can('manage organization') ||
            $user->accessibleProgramStudiIds()->isNotEmpty();
    }

    public function view(User $user, ProgramStudi $record): bool
    {
        return $user->isSuperAdmin() || $user->canAccessProgramStudi($record);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage organization');
    }

    public function update(User $user, ProgramStudi $record): bool
    {
        return $user->canAccessProgramStudi($record) &&
            ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, ProgramStudi $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, ProgramStudi $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, ProgramStudi $record): bool
    {
        return $user->isSuperAdmin();
    }
}
