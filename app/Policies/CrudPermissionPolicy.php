<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class CrudPermissionPolicy
{
    abstract protected function viewPermission(): string;

    protected function managePermission(): ?string
    {
        return null;
    }

    protected function readOnly(): bool
    {
        return false;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can($this->viewPermission()) || ($this->managePermission() !== null && $user->can($this->managePermission()));
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return ! $this->readOnly() && $this->canManage($user);
    }

    public function update(User $user, Model $record): bool
    {
        return ! $this->readOnly() && $this->canManage($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return ! $this->readOnly() && $this->canManage($user);
    }

    public function deleteAny(User $user): bool
    {
        return ! $this->readOnly() && $this->canManage($user);
    }

    protected function canManage(User $user): bool
    {
        return $user->isSuperAdmin() || ($this->managePermission() !== null && $user->can($this->managePermission()));
    }
}
