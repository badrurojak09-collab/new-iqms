<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\UserTenantScope;
use App\Support\Audit\AuditLogger;

final class UserTenantScopeObserver
{
    public function created(UserTenantScope $scope): void
    {
        app(AuditLogger::class)->record('scope.created', $scope, [], $scope->getAttributes());
    }

    public function updated(UserTenantScope $scope): void
    {
        app(AuditLogger::class)->record('scope.updated', $scope, $scope->getOriginal(), $scope->getChanges());
    }

    public function deleted(UserTenantScope $scope): void
    {
        app(AuditLogger::class)->record('scope.deleted', $scope, $scope->getAttributes());
    }
}
