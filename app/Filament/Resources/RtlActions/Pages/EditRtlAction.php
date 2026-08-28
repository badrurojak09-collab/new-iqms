<?php

namespace App\Filament\Resources\RtlActions\Pages;

use App\Filament\Resources\RtlActions\RtlActionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRtlAction extends EditRecord
{
    protected static string $resource = RtlActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
