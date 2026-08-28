<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReadinessRuns\RelationManagers;

use App\Domain\Accreditation\ReadinessGapResolutionService;
use App\Models\Evidence;
use App\Models\RtmMeeting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GapsRelationManager extends RelationManager
{
    protected static string $relationship = 'gaps';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('item_key')->label('Kunci Item')->searchable()->sortable(),
            TextColumn::make('gap_type')->label('Jenis Gap')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'evidence' => 'Evidence',
                'score' => 'Skor',
                'mapping' => 'Pemetaan',
                default => (string) $state,
            })->sortable(),
            TextColumn::make('severity')->label('Keparahan')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'critical' => 'Kritis',
                'high' => 'Tinggi',
                'medium' => 'Sedang',
                'low' => 'Rendah',
                default => (string) $state,
            })->sortable(),
            TextColumn::make('description')->label('Deskripsi')->wrap(),
            TextColumn::make('resolution_status')->label('Status Penyelesaian')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'open' => 'Terbuka',
                'in_progress' => 'Dalam Proses',
                'resolved' => 'Selesai',
                default => (string) $state,
            })->sortable(),
            TextColumn::make('rtl_actions_count')->label('Jumlah RTL')->counts('rtlActions')->sortable(),
            TextColumn::make('rtm_decisions_count')->label('Jumlah RTM')->counts('rtmDecisions')->sortable(),
            TextColumn::make('resolved_at')->label('Diselesaikan Pada')->dateTime()->sortable()->placeholder('—'),
        ])->headerActions([])->recordActions([
            Action::make('createRtl')
                ->label('Buat RTL')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn ($record): bool => auth()->user()?->can('resolve readiness gap') && $record->resolution_status !== 'resolved')
                ->form([
                    TextInput::make('code')->label('Kode RTL')->maxLength(80),
                    TextInput::make('title')->required()->maxLength(255),
                    Textarea::make('action_plan')->required()->rows(4),
                    DatePicker::make('due_date'),
                    Select::make('owner_id')->label('PIC')->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())->searchable(),
                ])
                ->action(function ($record, array $data): void {
                    app(ReadinessGapResolutionService::class)->createRtl($record, auth()->user(), $data);
                    Notification::make()->title('RTL berhasil dibuat dari readiness gap.')->success()->send();
                }),
            Action::make('attachCompletionEvidence')
                ->label('Lampirkan Bukti Penyelesaian')
                ->icon('heroicon-o-paper-clip')
                ->color('gray')
                ->visible(fn ($record): bool => auth()->user()?->can('resolve readiness gap') && $record->rtlActions()->exists())
                ->form([
                    Select::make('rtl_action_id')->label('Tindakan RTL')->options(fn ($record): array => $record->rtlActions()->pluck('title', 'id')->all())->required(),
                    Select::make('evidence_id')->label('Tautan Bukti Cloud')->options(function ($record): array {
                        $ptId = $record->run?->accreditation?->perguruan_tinggi_id;

                        if (! $ptId) {
                            return [];
                        }

                        return Evidence::query()->where('perguruan_tinggi_id', $ptId)->whereIn('status', ['verified', 'pending_review'])->orderBy('title')->pluck('title', 'id')->all();
                    })->searchable()->required(),
                    TextInput::make('label')->default('Evidence penyelesaian RTL'),
                ])
                ->action(function ($record, array $data): void {
                    app(ReadinessGapResolutionService::class)->attachCompletionEvidence($record, auth()->user(), (int) $data['rtl_action_id'], (int) $data['evidence_id'], $data['label'] ?? null);
                    Notification::make()->title('Bukti penyelesaian berhasil ditautkan ke RTL.')->success()->send();
                }),
            Action::make('createRtmDecision')
                ->label('Buat Keputusan RTM')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn ($record): bool => auth()->user()?->can('manage rtm') && $record->resolution_status !== 'resolved')
                ->form([
                    Select::make('rtm_meeting_id')->label('Rapat RTM')->options(function ($record): array {
                        $ptId = $record->run?->accreditation?->perguruan_tinggi_id;

                        if (! $ptId) {
                            return [];
                        }

                        return RtmMeeting::query()->where('perguruan_tinggi_id', $ptId)->orderByDesc('held_at')->pluck('title', 'id')->all();
                    })->searchable()->required(),
                    TextInput::make('code')->label('Kode Keputusan')->maxLength(80),
                    Textarea::make('decision')->required()->rows(4),
                    Textarea::make('rationale')->rows(3),
                ])
                ->action(function ($record, array $data): void {
                    app(ReadinessGapResolutionService::class)->createRtmDecision($record, auth()->user(), (int) $data['rtm_meeting_id'], $data);
                    Notification::make()->title('Keputusan RTM berhasil ditautkan ke readiness gap.')->success()->send();
                }),
            Action::make('resolveGap')
                ->label('Selesaikan Kesenjangan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record): bool => auth()->user()?->can('resolve readiness gap') && $record->resolution_status !== 'resolved')
                ->action(function ($record): void {
                    app(ReadinessGapResolutionService::class)->resolveGap($record, auth()->user());
                    Notification::make()->title('Readiness gap berhasil di-resolve.')->success()->send();
                }),
        ])->bulkActions([]);
    }
}
