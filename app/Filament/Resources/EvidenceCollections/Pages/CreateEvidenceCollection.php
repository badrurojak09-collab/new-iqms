<?php

namespace App\Filament\Resources\EvidenceCollections\Pages;

use App\Filament\Resources\EvidenceCollections\EvidenceCollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvidenceCollection extends CreateRecord
{
    protected static string $resource = EvidenceCollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] ??= auth()->id();

        return $data;
    }
}
