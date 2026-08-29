<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Accreditation;
use App\Models\User;

final class AccreditationPolicy
{
    /**
     * Memeriksa otorisasi awal (pre-authorization).
     * Jika user adalah Super Admin, langsung berikan akses penuh
     * tanpa perlu mengecek method otorisasi di bawahnya.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;  // Lanjutkan ke pengecekan method spesifik jika bukan Super Admin
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view accreditation') || $user->can('manage accreditation');
    }

    public function view(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation) &&
            ($user->can('view accreditation') || $user->can('manage accreditation') || $user->can('review accreditation'));
    }

    public function create(User $user): bool
    {
        return $user->can('manage accreditation');
    }

    public function update(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation) &&
            $user->can('manage accreditation') &&
            !in_array($accreditation->status, ['submitted', 'completed'], true);
    }

    public function delete(User $user, Accreditation $accreditation): bool
    {
        return $this->update($user, $accreditation);
    }

    public function review(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation) &&
            $user->can('review accreditation');
    }

    public function approve(User $user, Accreditation $accreditation): bool
    {
        return $this->hasTenantAccess($user, $accreditation) &&
            $user->can('approve accreditation');
    }

    private function hasTenantAccess(User $user, Accreditation $accreditation): bool
    {
        // Pengecekan super admin di sini sudah tidak diperlukan lagi
        // karena sudah ditangani oleh method before() di atas.

        if (!$user->canAccessPerguruanTinggi($accreditation->perguruanTinggi)) {
            return false;
        }

        return $accreditation->program_studi_id === null ||
            $user->canAccessProgramStudi($accreditation->programStudi);
    }
}
