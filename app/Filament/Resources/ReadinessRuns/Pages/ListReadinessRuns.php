<?php

namespace App\Filament\Resources\ReadinessRuns\Pages;

use App\Filament\Resources\ReadinessRuns\ReadinessRunResource;
use Filament\Resources\Pages\ListRecords;

class ListReadinessRuns extends ListRecords
{
    protected static string $resource = ReadinessRunResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
