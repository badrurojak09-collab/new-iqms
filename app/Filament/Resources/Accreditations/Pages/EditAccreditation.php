<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\Pages;

use App\Domain\Accreditation\LedLkpsValidator;
use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Filament\Resources\Accreditations\AccreditationResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAccreditation extends EditRecord
{
    protected static string $resource = AccreditationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('validateLedLkps')
                ->label('Validasi LED/LKPS')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->action(function (): void {
                    $result = app(LedLkpsValidator::class)->validate($this->record);
                    $notification = Notification::make()->title($result['valid'] ? 'Validasi berhasil' : 'Validasi menemukan masalah');
                    $notification = $result['valid'] ? $notification->success() : $notification->danger();
                    if ($result['errors'] !== []) {
                        $notification->body(collect($result['errors'])->take(5)->map(fn (array $error): string => $error['key'].': '.$error['message'])->implode("\n"));
                    }
                    $notification->send();
                }),
            Action::make('calculateScore')
                ->label('Hitung Skor')
                ->icon('heroicon-o-calculator')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->requiresConfirmation()
                ->action(function (): void {
                    $snapshot = app(RuntimeScoringEngine::class)->scoreAndPersist($this->record, auth()->id());
                    Notification::make()->title('Snapshot skor berhasil disimpan: '.number_format((float) $snapshot->score, 2))->success()->body('Hash integritas snapshot: '.$snapshot->snapshot_hash)->send();
                }),
            Action::make('save')->label('Simpan')->submit('save'),
        ];
    }
}
