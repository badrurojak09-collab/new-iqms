<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Pages;

use App\Filament\Resources\AmiChecklistItems\AmiChecklistItemResource;
use Filament\Resources\Pages\EditRecord;

class EditAmiChecklistItem extends EditRecord
{
    protected static string $resource = AmiChecklistItemResource::class;
}
