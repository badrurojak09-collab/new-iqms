<?php

namespace App\Filament\Resources\ReadinessRuns\Pages;

use App\Filament\Resources\ReadinessRuns\ReadinessRunResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListReadinessRuns extends ListRecords
{
    protected static string $resource = ReadinessRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
