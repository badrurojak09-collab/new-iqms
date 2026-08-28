<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators\Pages;

use App\Filament\Resources\SpmiIndicators\SpmiIndicatorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiIndicators extends ListRecords
{
    protected static string $resource = SpmiIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat SpmiIndicator')];
    }
}
