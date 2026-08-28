<?php

namespace App\Filament\Resources\ReadinessRuns\Pages;

use App\Filament\Resources\ReadinessRuns\ReadinessRunResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReadinessRun extends EditRecord
{
    protected static string $resource = ReadinessRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
