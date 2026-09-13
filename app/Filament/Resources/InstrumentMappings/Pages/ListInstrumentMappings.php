<?php

namespace App\Filament\Resources\InstrumentMappings\Pages;

use App\Filament\Resources\InstrumentMappings\InstrumentMappingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInstrumentMappings extends ListRecords
{
    protected static string $resource = InstrumentMappingResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
