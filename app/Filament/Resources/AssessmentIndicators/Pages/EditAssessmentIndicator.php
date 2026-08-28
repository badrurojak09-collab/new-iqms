<?php

namespace App\Filament\Resources\AssessmentIndicators\Pages;

use App\Filament\Resources\AssessmentIndicators\AssessmentIndicatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentIndicator extends EditRecord
{
    protected static string $resource = AssessmentIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
