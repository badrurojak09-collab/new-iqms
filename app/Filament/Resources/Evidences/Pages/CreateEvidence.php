<?php

namespace App\Filament\Resources\Evidences\Pages;

use App\Filament\Resources\Evidences\EvidenceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvidence extends CreateRecord
{
    protected static string $resource = EvidenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
