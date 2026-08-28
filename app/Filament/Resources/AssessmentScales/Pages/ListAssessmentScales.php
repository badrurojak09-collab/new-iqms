<?php

namespace App\Filament\Resources\AssessmentScales\Pages;

use App\Filament\Resources\AssessmentScales\AssessmentScaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentScales extends ListRecords
{
    protected static string $resource = AssessmentScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
