<?php

namespace App\Filament\Resources\AssessmentElements\Pages;

use App\Filament\Resources\AssessmentElements\AssessmentElementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssessmentElements extends ListRecords
{
    protected static string $resource = AssessmentElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
