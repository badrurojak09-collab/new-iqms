<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Pages;

use App\Filament\Resources\RtmMeetings\RtmMeetingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRtmMeeting extends CreateRecord
{
    protected static string $resource = RtmMeetingResource::class;
}
