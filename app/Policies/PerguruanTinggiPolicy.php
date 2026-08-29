<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\PerguruanTinggi;
use App\Models\User;

final class PerguruanTinggiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() ||
            $user->can('manage organization') ||
            $user->accessiblePerguruanTinggiIds()->isNotEmpty();
    }

    public function view(User $user, PerguruanTinggi $record): bool
    {
        return $user->isSuperAdmin() || $user->canAccessPerguruanTinggi($record);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage organization');
    }

    public function update(User $user, PerguruanTinggi $record): bool
    {
        return $user->canAccessPerguruanTinggi($record) &&
            ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, PerguruanTinggi $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, PerguruanTinggi $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, PerguruanTinggi $record): bool
    {
        return $user->isSuperAdmin();
    }
}
