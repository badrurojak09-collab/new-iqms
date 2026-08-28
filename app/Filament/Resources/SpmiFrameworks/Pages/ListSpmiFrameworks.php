<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks\Pages;

use App\Filament\Resources\SpmiFrameworks\SpmiFrameworkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiFrameworks extends ListRecords
{
    protected static string $resource = SpmiFrameworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Framework SPMI'),
        ];
    }
}

