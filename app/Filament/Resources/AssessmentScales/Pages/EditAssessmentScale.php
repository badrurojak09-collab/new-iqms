<?php

namespace App\Filament\Resources\AssessmentScales\Pages;

use App\Filament\Resources\AssessmentScales\AssessmentScaleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentScale extends EditRecord
{
    protected static string $resource = AssessmentScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
