<?php

namespace App\Filament\Resources\InstrumentFamilies\Pages;

use App\Filament\Resources\InstrumentFamilies\InstrumentFamilyResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInstrumentFamilies extends ListRecords
{
    protected static string $resource = InstrumentFamilyResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
