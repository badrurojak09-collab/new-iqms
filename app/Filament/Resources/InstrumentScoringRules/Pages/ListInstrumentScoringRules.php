<?php

namespace App\Filament\Resources\InstrumentScoringRules\Pages;

use App\Filament\Resources\InstrumentScoringRules\InstrumentScoringRuleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListInstrumentScoringRules extends ListRecords
{
    protected static string $resource = InstrumentScoringRuleResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
