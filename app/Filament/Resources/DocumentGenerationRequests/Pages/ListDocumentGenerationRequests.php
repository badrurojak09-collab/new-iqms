<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentGenerationRequests\Pages;

use App\Filament\Resources\DocumentGenerationRequests\DocumentGenerationRequestResource;
use Filament\Resources\Pages\ListRecords;

final class ListDocumentGenerationRequests extends ListRecords
{
    protected static string $resource = DocumentGenerationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
