<?php

namespace App\Filament\Resources\AssessmentCriteria\Pages;

use App\Filament\Resources\AssessmentCriteria\AssessmentCriterionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentCriterion extends EditRecord
{
    protected static string $resource = AssessmentCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
