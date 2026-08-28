<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks\Pages;

use App\Filament\Resources\SpmiFrameworks\SpmiFrameworkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiFramework extends EditRecord
{
    protected static string $resource = SpmiFrameworkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Hapus'),
            ForceDeleteAction::make()->label('Hapus Permanen'),
            RestoreAction::make()->label('Pulihkan'),
        ];
    }
}

