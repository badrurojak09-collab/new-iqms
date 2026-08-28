<?php

namespace App\Filament\Resources\UserTenantScopes\Pages;

use App\Filament\Resources\UserTenantScopes\UserTenantScopeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserTenantScope extends CreateRecord
{
    protected static string $resource = UserTenantScopeResource::class;
}
