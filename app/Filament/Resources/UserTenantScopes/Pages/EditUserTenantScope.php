<?php

namespace App\Filament\Resources\UserTenantScopes\Pages;

use App\Filament\Resources\UserTenantScopes\UserTenantScopeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserTenantScope extends EditRecord
{
    protected static string $resource = UserTenantScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
