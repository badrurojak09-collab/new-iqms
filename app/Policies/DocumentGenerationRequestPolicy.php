<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentGenerationRequest;
use App\Models\User;

final class DocumentGenerationRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, DocumentGenerationRequest $request): bool
    {
        if (! $this->canView($user)) return false;
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) return true;
        if ($request->perguruan_tinggi_id && method_exists($user, 'canAccessPerguruanTinggi')) return $user->canAccessPerguruanTinggi($request->perguruan_tinggi_id);
        return true;
    }

    public function create(User $user): bool { return false; }
    public function update(User $user, DocumentGenerationRequest $request): bool { return false; }
    public function delete(User $user, DocumentGenerationRequest $request): bool { return false; }
    public function deleteAny(User $user): bool { return false; }

    private function canView(User $user): bool
    {
        return (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())
            || $user->can('view security')
            || $user->can('view spmi')
            || $user->can('view ami')
            || $user->can('view evidence')
            || $user->can('view accreditation');
    }
}
