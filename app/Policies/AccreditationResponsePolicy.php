<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AccreditationResponse;
use App\Models\User;
use App\Support\Tenancy\TenantQuery;

final class AccreditationResponsePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('view accreditation') || $user->can('manage accreditation') || $user->can('review accreditation');
    }

    public function view(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && ($user->can('view accreditation') || $user->can('manage accreditation') || $user->can('review accreditation'));
    }

    public function create(User $user): bool
    {
        return $user->can('manage accreditation');
    }

    public function update(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && $user->can('manage accreditation')
            && ! $response->isLocked()
            && ! in_array($response->status, [AccreditationResponse::STATUS_SUBMITTED, AccreditationResponse::STATUS_IN_REVIEW, AccreditationResponse::STATUS_APPROVED], true);
    }

    public function submit(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && $user->can('manage accreditation')
            && ! $response->isLocked()
            && in_array($response->status, [AccreditationResponse::STATUS_DRAFT, AccreditationResponse::STATUS_REVISION_REQUIRED, AccreditationResponse::STATUS_REJECTED], true);
    }

    public function review(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && $user->can('review accreditation')
            && in_array($response->status, [AccreditationResponse::STATUS_SUBMITTED, AccreditationResponse::STATUS_IN_REVIEW], true);
    }

    public function requestRevision(User $user, AccreditationResponse $response): bool
    {
        return $this->review($user, $response);
    }

    public function approve(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && $user->can('approve accreditation')
            && $response->status === AccreditationResponse::STATUS_IN_REVIEW;
    }

    public function reject(User $user, AccreditationResponse $response): bool
    {
        return $this->approve($user, $response);
    }

    public function lock(User $user, AccreditationResponse $response): bool
    {
        return $this->hasTenantAccess($user, $response)
            && $user->can('approve accreditation')
            && $response->status === AccreditationResponse::STATUS_APPROVED;
    }

    private function hasTenantAccess(User $user, AccreditationResponse $response): bool
    {
        $accreditation = $response->relationLoaded('accreditation')
            ? $response->accreditation
            : $response->accreditation()->first();

        return $accreditation !== null
            && TenantQuery::canAccessTenantRecord($user, $accreditation->perguruan_tinggi_id, $accreditation->program_studi_id);
    }
}
