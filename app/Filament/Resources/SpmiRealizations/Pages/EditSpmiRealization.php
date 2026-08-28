<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Pages;

use App\Filament\Resources\SpmiRealizations\SpmiRealizationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiRealization extends EditRecord
{
    protected static string $resource = SpmiRealizationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')];
    }
}
