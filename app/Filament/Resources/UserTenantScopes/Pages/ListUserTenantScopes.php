<?php

namespace App\Filament\Resources\UserTenantScopes\Pages;

use App\Filament\Resources\UserTenantScopes\UserTenantScopeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserTenantScopes extends ListRecords
{
    protected static string $resource = UserTenantScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
