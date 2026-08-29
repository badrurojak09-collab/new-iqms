<?php

namespace App\Filament\Resources\LkpsTemplates\Pages;

use App\Filament\Resources\LkpsTemplates\LkpsTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLkpsTemplates extends ListRecords
{
    protected static string $resource = LkpsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Template LKPS'),
        ];
    }
}
