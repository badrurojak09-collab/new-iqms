<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\Pages;

use App\Filament\Resources\SpmiEvaluations\SpmiEvaluationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpmiEvaluation extends EditRecord
{
    protected static string $resource = SpmiEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->label('Hapus')];
    }
}
