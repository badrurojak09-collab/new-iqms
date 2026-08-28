<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Pages;

use App\Filament\Resources\AmiChecklistItems\AmiChecklistItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAmiChecklistItem extends CreateRecord
{
    protected static string $resource = AmiChecklistItemResource::class;
}
