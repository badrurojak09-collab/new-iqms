<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Pages;

use App\Filament\Resources\SpmiRealizations\SpmiRealizationResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSpmiRealization extends CreateRecord
{
    protected static string $resource = SpmiRealizationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['recorded_by'] ??= Auth::id();

        return $data;
    }
}
