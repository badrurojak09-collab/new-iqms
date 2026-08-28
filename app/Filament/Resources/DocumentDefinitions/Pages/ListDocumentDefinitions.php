<?php

namespace App\Filament\Resources\DocumentDefinitions\Pages;

use App\Filament\Resources\DocumentDefinitions\DocumentDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentDefinitions extends ListRecords
{
    protected static string $resource = DocumentDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
