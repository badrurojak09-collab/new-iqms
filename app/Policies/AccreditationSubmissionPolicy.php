<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AccreditationSubmission;
use App\Models\User;

final class AccreditationSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('view accreditation') || $user->can('review accreditation');
    }

    public function view(User $user, AccreditationSubmission $submission): bool
    {
        $accreditation = $submission->loadMissing('accreditation')->accreditation;

        return $accreditation !== null
            && ($user->isSuperAdmin() || $user->can('view accreditation') || $user->can('review accreditation'))
            && $user->canAccessPerguruanTinggi($accreditation->perguruanTinggi)
            && ($accreditation->program_studi_id === null || $user->canAccessProgramStudi($accreditation->programStudi));
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->can('manage accreditation');
    }

    public function update(User $user, AccreditationSubmission $submission): bool
    {
        return $this->view($user, $submission)
            && $submission->status === 'draft'
            && ($user->isSuperAdmin() || $user->can('manage accreditation'));
    }

    public function delete(User $user, AccreditationSubmission $submission): bool
    {
        return $this->update($user, $submission);
    }

    public function approve(User $user, AccreditationSubmission $submission): bool
    {
        return $this->view($user, $submission)
            && $submission->status === 'review'
            && ($user->isSuperAdmin() || $user->can('approve accreditation'));
    }

    public function submit(User $user, AccreditationSubmission $submission): bool
    {
        return $this->view($user, $submission)
            && in_array($submission->status, ['draft', 'review', 'approved'], true)
            && ($user->isSuperAdmin() || $user->can('manage accreditation'));
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
