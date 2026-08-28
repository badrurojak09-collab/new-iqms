<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Pages;

use App\Filament\Resources\AmiChecklistItems\AmiChecklistItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiChecklistItems extends ListRecords
{
    protected static string $resource = AmiChecklistItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat Checklist Audit')];
    }
}
