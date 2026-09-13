<?php

declare(strict_types=1);

namespace App\Filament\Resources\PerguruanTinggiStandards\Pages;

use App\Filament\Resources\PerguruanTinggiStandards\PerguruanTinggiStandardResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

final class ListPerguruanTinggiStandards extends ListRecords
{
    protected static string $resource = PerguruanTinggiStandardResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah'),
        ];
    }
}
