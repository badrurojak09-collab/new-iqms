<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\Pages;

use App\Filament\Resources\SpmiEvaluations\SpmiEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSpmiEvaluations extends ListRecords
{
    protected static string $resource = SpmiEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat SpmiEvaluation')];
    }
}
