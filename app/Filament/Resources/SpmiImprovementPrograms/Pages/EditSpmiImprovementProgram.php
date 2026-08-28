<?php

namespace App\Filament\Resources\SpmiImprovementPrograms\Pages;

use App\Filament\Resources\SpmiImprovementPrograms\SpmiImprovementProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiImprovementProgram extends EditRecord
{
    protected static string $resource = SpmiImprovementProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
