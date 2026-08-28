<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiStandards\Pages;

use App\Filament\Resources\SpmiStandards\SpmiStandardResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiStandard extends EditRecord
{
    protected static string $resource = SpmiStandardResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')];
    }
}
