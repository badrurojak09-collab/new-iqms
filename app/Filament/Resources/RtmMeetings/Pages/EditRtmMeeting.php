<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Pages;

use App\Filament\Resources\RtmMeetings\RtmMeetingResource;
use Filament\Resources\Pages\EditRecord;

class EditRtmMeeting extends EditRecord
{
    protected static string $resource = RtmMeetingResource::class;
}
