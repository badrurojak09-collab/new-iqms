<?php

namespace App\Filament\Resources\AssessmentCriteria\Pages;

use App\Filament\Resources\AssessmentCriteria\AssessmentCriterionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentCriteria extends ListRecords
{
    protected static string $resource = AssessmentCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
