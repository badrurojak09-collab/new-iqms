<?php

namespace App\Filament\Resources\LedTemplates\Pages;

use App\Filament\Resources\LedTemplates\LedTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLedTemplates extends ListRecords
{
    protected static string $resource = LedTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
