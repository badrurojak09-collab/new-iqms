<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets\Pages;

use App\Filament\Resources\SpmiTargets\SpmiTargetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiTarget extends EditRecord
{
    protected static string $resource = SpmiTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')];
    }
}
