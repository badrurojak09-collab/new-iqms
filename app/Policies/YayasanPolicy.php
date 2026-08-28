<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Yayasan;

final class YayasanPolicy
{
    public function view(User $user, Yayasan $record): bool
    {
        return $user->isSuperAdmin() || $user->yayasan_id === $record->getKey();
    }

    public function update(User $user, Yayasan $record): bool
    {
        return $this->view($user, $record)
            && ($user->isSuperAdmin() || $user->can('manage organization'));
    }

    public function delete(User $user, Yayasan $record): bool
    {
        return $this->update($user, $record);
    }
}
