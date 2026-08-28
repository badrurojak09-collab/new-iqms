<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Pages;

use App\Filament\Resources\SpmiRealizations\SpmiRealizationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiRealizations extends ListRecords
{
    protected static string $resource = SpmiRealizationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat SpmiRealization')];
    }
}
