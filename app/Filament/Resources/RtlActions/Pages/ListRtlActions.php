<?php

namespace App\Filament\Resources\RtlActions\Pages;

use App\Filament\Resources\RtlActions\RtlActionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRtlActions extends ListRecords
{
    protected static string $resource = RtlActionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
