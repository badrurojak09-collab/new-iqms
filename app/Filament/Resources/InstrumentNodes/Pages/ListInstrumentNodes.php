<?php

namespace App\Filament\Resources\InstrumentNodes\Pages;

use App\Filament\Resources\InstrumentNodes\InstrumentNodeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInstrumentNodes extends ListRecords
{
    protected static string $resource = InstrumentNodeResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
