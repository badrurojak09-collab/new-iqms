<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\Pages;

use App\Filament\Resources\AmiCycles\AmiCycleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiCycles extends ListRecords
{
    protected static string $resource = AmiCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat Siklus AMI')];
    }
}
