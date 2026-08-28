<?php

namespace App\Filament\Resources\AuditLogs\Pages;

use App\Filament\Resources\AuditLogs\AuditLogResource;
use Filament\Resources\Pages\EditRecord;

class ViewAuditLog extends EditRecord
{
    protected static string $resource = AuditLogResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->form->disabled();
    }
}
