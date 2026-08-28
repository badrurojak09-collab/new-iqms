<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAmiFinding extends CreateRecord
{
    protected static string $resource = AmiFindingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reported_by'] ??= auth()->id();

        return $data;
    }
}
