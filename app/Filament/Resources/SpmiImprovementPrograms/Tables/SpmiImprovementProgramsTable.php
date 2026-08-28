<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiImprovementPrograms\Tables;

use App\Domain\Quality\SpmiImprovementProgramLifecycleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpmiImprovementProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable()->copyable(),
            TextColumn::make('title')->searchable()->wrap(),
            TextColumn::make('indicator.code')->label('Indikator')->placeholder('—'),
            TextColumn::make('target.period_year')->label('Tahun Target')->placeholder('—'),
            TextColumn::make('status')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->sortable(),
            TextColumn::make('progress_percent')->suffix('%')->sortable(),
            TextColumn::make('due_date')->date()->sortable(),
            TextColumn::make('reEvaluatedReadinessRun.id')->label('Proses Evaluasi Ulang')->placeholder('—'),
            TextColumn::make('re_evaluation_status')->label('Proses Kesiapan')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->placeholder('—')->color(fn (?string $state): string => match ($state) { 'completed' => 'success', 'failed' => 'danger', 'running' => 'warning', default => 'gray', }),
            TextColumn::make('re_evaluated_at')->dateTime()->placeholder('—'),
        ])->recordActions([
            Action::make('start')->label('Mulai')->color('info')->visible(fn ($record): bool => (auth()->user()?->can('manage spmi') ?? false) && $record->status === 'planned')->action(function ($record): void {
                app(SpmiImprovementProgramLifecycleService::class)->transition($record, auth()->user(), 'in_progress');
                Notification::make()->title('Program peningkatan dimulai.')->success()->send();
            }),
            Action::make('complete')->label('Selesaikan')->color('warning')->visible(fn ($record): bool => (auth()->user()?->can('manage spmi') ?? false) && $record->status === 'in_progress')->form([Textarea::make('notes')->required()->label('Catatan Penyelesaian')])->action(function ($record, array $data): void {
                app(SpmiImprovementProgramLifecycleService::class)->transition($record, auth()->user(), 'completed', $data['notes']);
                Notification::make()->title('Program ditandai completed.')->success()->send();
            }),
            Action::make('verify')->label('Verifikasi')->color('success')->visible(fn ($record): bool => (auth()->user()?->can('verify spmi improvement') ?? false) && $record->status === 'completed')->requiresConfirmation()->action(function ($record): void {
                $program = app(SpmiImprovementProgramLifecycleService::class)->transition($record, auth()->user(), 'verified');
                Notification::make()->title($program->re_evaluation_status === 'queued' ? 'Program verified. Readiness masuk antrean proses.' : 'Program berhasil diverifikasi.')->success()->send();
            }),
        ])->headerActions([])->toolbarActions([]);
    }
}
