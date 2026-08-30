<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentRubrics\Tables;

use App\Domain\InstrumentRegistry\ApproveAssessmentConfiguration;
use App\Support\Ui\StatusLabel;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentRubricsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['instrumentVersion', 'approver']))
            ->columns([
                TextColumn::make('label')->label('Label Rubrik')->searchable()->sortable(),
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
                TextColumn::make('min_score')->label('Nilai Minimum')->sortable(),
                TextColumn::make('max_score')->label('Nilai Maksimum')->sortable(),
                TextColumn::make('status')->label('Status Rubrik')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('approver.name')->label('Disetujui Oleh')->placeholder('—'),
                TextColumn::make('approved_at')->label('Disetujui Pada')->dateTime()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status Rubrik')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku']),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->visible(fn ($record): bool => (auth()->user()?->can('approve instrument configuration') ?? false) && ! in_array($record->status, ['approved', 'retired'], true))
                    ->form([Textarea::make('approval_notes')->label('Catatan Persetujuan')->required()])
                    ->action(function ($record, array $data): void {
                        app(ApproveAssessmentConfiguration::class)->approveRubric($record, auth()->user(), $data['approval_notes']);
                        Notification::make()->title('Rubrik berhasil disetujui.')->success()->send();
                    }),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }
}
