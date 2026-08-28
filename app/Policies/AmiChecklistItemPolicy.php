<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AmiChecklistItem;
use App\Models\User;

final class AmiChecklistItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view ami');
    }

    public function view(User $user, AmiChecklistItem $item): bool
    {
        return $user->can('view ami') && $user->canAccessPerguruanTinggi($item->cycle->perguruanTinggi);
    }

    public function create(User $user): bool
    {
        return $user->can('manage ami');
    }

    public function update(User $user, AmiChecklistItem $item): bool
    {
        return $user->can('manage ami') && $user->canAccessPerguruanTinggi($item->cycle->perguruanTinggi);
    }

    public function delete(User $user, AmiChecklistItem $item): bool
    {
        return $user->can('manage ami') && $user->canAccessPerguruanTinggi($item->cycle->perguruanTinggi);
    }
}
