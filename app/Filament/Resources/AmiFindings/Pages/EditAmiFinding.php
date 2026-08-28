<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Pages;

use App\Filament\Resources\AmiFindings\AmiFindingResource;
use Filament\Resources\Pages\EditRecord;

class EditAmiFinding extends EditRecord
{
    protected static string $resource = AmiFindingResource::class;
}
