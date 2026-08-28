<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PerguruanTinggi;
use App\Models\User;

final class PerguruanTinggiPolicy
{
    public function view(User $user, PerguruanTinggi $record): bool
    {
        return $user->canAccessPerguruanTinggi($record);
    }

    public function update(User $user, PerguruanTinggi $record): bool
    {
        return $user->canAccessPerguruanTinggi($record)
            && ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, PerguruanTinggi $record): bool
    {
        return $this->update($user, $record);
    }
}
