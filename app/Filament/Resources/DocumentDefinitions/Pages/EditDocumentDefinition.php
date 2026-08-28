<?php

namespace App\Filament\Resources\DocumentDefinitions\Pages;

use App\Filament\Resources\DocumentDefinitions\DocumentDefinitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentDefinition extends EditRecord
{
    protected static string $resource = DocumentDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
