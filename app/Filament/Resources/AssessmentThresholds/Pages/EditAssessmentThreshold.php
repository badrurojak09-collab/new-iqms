<?php

namespace App\Filament\Resources\AssessmentThresholds\Pages;

use App\Filament\Resources\AssessmentThresholds\AssessmentThresholdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentThreshold extends EditRecord
{
    protected static string $resource = AssessmentThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
