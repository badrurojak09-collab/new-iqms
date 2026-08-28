<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SpmiImprovementProgram;
use App\Models\User;

class SpmiImprovementProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view spmi') || $user->can('manage spmi');
    }

    public function view(User $user, SpmiImprovementProgram $program): bool
    {
        return $user->can('view spmi') || $user->can('manage spmi');
    }

    public function create(User $user): bool
    {
        return $user->can('manage spmi');
    }

    public function update(User $user, SpmiImprovementProgram $program): bool
    {
        return $user->can('manage spmi') && ! in_array($program->status, ['verified'], true);
    }

    public function transition(User $user, SpmiImprovementProgram $program, string $toStatus): bool
    {
        return $user->can($toStatus === 'verified' ? 'verify spmi improvement' : 'manage spmi');
    }
}
