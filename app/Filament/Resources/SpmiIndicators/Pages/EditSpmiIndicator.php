<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators\Pages;

use App\Filament\Resources\SpmiIndicators\SpmiIndicatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiIndicator extends EditRecord
{
    protected static string $resource = SpmiIndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')];
    }
}
