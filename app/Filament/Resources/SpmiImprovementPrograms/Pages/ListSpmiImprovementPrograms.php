<?php

namespace App\Filament\Resources\SpmiImprovementPrograms\Pages;

use App\Filament\Resources\SpmiImprovementPrograms\SpmiImprovementProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiImprovementPrograms extends ListRecords
{
    protected static string $resource = SpmiImprovementProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
