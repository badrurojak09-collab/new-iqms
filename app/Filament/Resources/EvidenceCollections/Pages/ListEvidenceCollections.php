<?php

namespace App\Filament\Resources\EvidenceCollections\Pages;

use App\Filament\Resources\EvidenceCollections\EvidenceCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEvidenceCollections extends ListRecords
{
    protected static string $resource = EvidenceCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
