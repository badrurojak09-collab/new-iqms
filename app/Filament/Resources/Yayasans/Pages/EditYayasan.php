<?php

namespace App\Filament\Resources\Yayasans\Pages;

use App\Filament\Resources\Yayasans\YayasanResource;
use Filament\Resources\Pages\EditRecord;

class EditYayasan extends EditRecord
{
    protected static string $resource = YayasanResource::class;

    protected function getFormActions(): array
    {
        return [];
    }
}
