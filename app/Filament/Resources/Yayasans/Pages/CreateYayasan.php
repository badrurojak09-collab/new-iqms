<?php

namespace App\Filament\Resources\Yayasans\Pages;

use App\Filament\Resources\Yayasans\YayasanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateYayasan extends CreateRecord
{
    protected static string $resource = YayasanResource::class;

    /**
     * Sembunyikan tombol action bawaan yang ada di luar card/section.
     */
    protected function getFormActions(): array
    {
        return [];
    }
}
