<?php

namespace App\Filament\Resources\AssessmentIndicators\Pages;

use App\Filament\Resources\AssessmentIndicators\AssessmentIndicatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentIndicators extends ListRecords
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
