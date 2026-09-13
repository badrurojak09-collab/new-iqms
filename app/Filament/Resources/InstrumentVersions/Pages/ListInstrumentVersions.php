<?php

namespace App\Filament\Resources\InstrumentVersions\Pages;

use App\Filament\Resources\InstrumentVersions\InstrumentVersionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInstrumentVersions extends ListRecords
{
    protected static string $resource = InstrumentVersionResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
