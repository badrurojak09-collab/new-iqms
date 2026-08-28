<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\Pages;

use App\Filament\Resources\AmiCycles\AmiCycleResource;
use Filament\Resources\Pages\EditRecord;

class EditAmiCycle extends EditRecord
{
    protected static string $resource = AmiCycleResource::class;
}
