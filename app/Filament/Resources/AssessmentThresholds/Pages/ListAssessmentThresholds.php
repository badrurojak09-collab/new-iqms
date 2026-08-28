<?php

namespace App\Filament\Resources\AssessmentThresholds\Pages;

use App\Filament\Resources\AssessmentThresholds\AssessmentThresholdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentThresholds extends ListRecords
{
    protected static string $resource = AssessmentThresholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
