<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Yayasan;

final class YayasanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() ||
            $user->can('manage organization') ||
            $user->accessibleYayasanIds()->isNotEmpty();
    }

    public function view(User $user, Yayasan $record): bool
    {
        return $user->isSuperAdmin() || $user->canAccessYayasan($record);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage organization');
    }

    public function update(User $user, Yayasan $record): bool
    {
        return $user->canAccessYayasan($record) &&
            ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, Yayasan $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Yayasan $record): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Yayasan $record): bool
    {
        return $user->isSuperAdmin();
    }
}
