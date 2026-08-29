<?php

namespace App\Filament\Resources\AccreditationCriteria\Pages;

use App\Filament\Resources\AccreditationCriteria\AccreditationCriterionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccreditationCriteria extends ListRecords
{
    protected static string $resource = AccreditationCriterionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
