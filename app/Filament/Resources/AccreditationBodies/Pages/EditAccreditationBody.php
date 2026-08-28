<?php

namespace App\Filament\Resources\AccreditationBodies\Pages;

use App\Filament\Resources\AccreditationBodies\AccreditationBodyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditAccreditationBody extends EditRecord
{
    protected static string $resource = AccreditationBodyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
