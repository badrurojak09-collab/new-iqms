<?php

namespace App\Filament\Resources\EvidenceCollections\Pages;

use App\Filament\Resources\EvidenceCollections\EvidenceCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEvidenceCollection extends EditRecord
{
    protected static string $resource = EvidenceCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
