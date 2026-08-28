<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\Pages;

use App\Filament\Resources\SpmiEvaluations\SpmiEvaluationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSpmiEvaluation extends CreateRecord
{
    protected static string $resource = SpmiEvaluationResource::class;
}
