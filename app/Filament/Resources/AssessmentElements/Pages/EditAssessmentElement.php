<?php

namespace App\Filament\Resources\AssessmentElements\Pages;

use App\Filament\Resources\AssessmentElements\AssessmentElementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssessmentElement extends EditRecord
{
    protected static string $resource = AssessmentElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
