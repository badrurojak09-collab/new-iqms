<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAmiFindings extends ListRecords
{
    protected static string $resource = AmiFindingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat Temuan AMI')];
    }
}
