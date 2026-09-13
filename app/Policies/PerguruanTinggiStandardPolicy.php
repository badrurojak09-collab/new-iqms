<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PerguruanTinggiStandard;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

final class PerguruanTinggiStandardPolicy extends CrudPermissionPolicy
{
    protected function viewPermission(): string
    {
        return 'view perguruan tinggi standards';
    }

    protected function managePermission(): ?string
    {
        return 'manage perguruan tinggi standards';
    }

    protected function readOnly(): bool
    {
        return false;
    }

    public function view(User $user, Model $record): bool
    {
        return $record instanceof PerguruanTinggiStandard
            && $user->can($this->viewPermission())
            && $this->samePerguruanTinggi($user, $record);
    }

    public function update(User $user, Model $record): bool
    {
        return $record instanceof PerguruanTinggiStandard
            && $user->can($this->managePermission())
            && $this->samePerguruanTinggi($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->update($user, $record);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->update($user, $record);
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $this->update($user, $record);
    }

    private function samePerguruanTinggi(User $user, PerguruanTinggiStandard $standard): bool
    {
        return $user->isSuperAdmin() || in_array((int) $standard->perguruan_tinggi_id, $user->accessiblePerguruanTinggiIds()->all(), true);
    }
}
