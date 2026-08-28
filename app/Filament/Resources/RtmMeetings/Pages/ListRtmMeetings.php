<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Pages;

use App\Filament\Resources\RtmMeetings\RtmMeetingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRtmMeetings extends ListRecords
{
    protected static string $resource = RtmMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Buat Rapat RTM')];
    }
}
