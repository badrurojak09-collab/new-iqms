<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets\Pages;

use App\Filament\Resources\SpmiTargets\SpmiTargetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiTargets extends ListRecords
{
    protected static string $resource = SpmiTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat SpmiTarget')];
    }
}
