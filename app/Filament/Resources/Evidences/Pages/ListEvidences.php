<?php

namespace App\Filament\Resources\Evidences\Pages;

use App\Filament\Resources\Evidences\EvidenceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListEvidences extends ListRecords
{
    protected static string $resource = EvidenceResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
