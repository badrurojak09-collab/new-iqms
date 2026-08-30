<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentThresholds\Tables;

use App\Domain\InstrumentRegistry\ApproveAssessmentConfiguration;
use App\Support\Ui\StatusLabel;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentThresholdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['instrumentVersion', 'element', 'indicator']))
            ->columns([
                TextColumn::make('code')->label('Kode Ambang Batas')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('Nama Ambang Batas')->searchable()->wrap(),
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
                TextColumn::make('element.code')->label('Kode Elemen')->placeholder('—'),
                TextColumn::make('indicator.code')->label('Kode Indikator')->placeholder('—'),
                TextColumn::make('comparison')->label('Pembanding')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'gte' => 'Lebih Besar atau Sama Dengan', 'lte' => 'Lebih Kecil atau Sama Dengan', 'eq' => 'Sama Dengan', 'between' => 'Di Antara', 'target_match' => 'Sesuai Target', default => $state ?: '—',
                })->badge()->sortable(),
                TextColumn::make('direction')->label('Arah Evaluasi')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'higher_is_better' => 'Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target', default => 'Otomatis',
                })->badge(),
                TextColumn::make('target_value')->label('Nilai Target')->sortable()->placeholder('—'),
                TextColumn::make('minimum_score')->label('Skor Minimum')->sortable()->placeholder('—'),
                TextColumn::make('status')->label('Status Konfigurasi')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status Konfigurasi')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku']),
                SelectFilter::make('direction')->label('Arah Evaluasi')->options(['auto' => 'Otomatis', 'higher_is_better' => 'Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target']),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                Action::make('approve')->label('Setujui')->color('success')->visible(fn ($record): bool => (auth()->user()?->can('approve instrument configuration') ?? false) && ! in_array($record->status, ['approved', 'retired'], true))->form([Textarea::make('approval_notes')->label('Catatan Persetujuan')->required()])->action(function ($record, array $data): void {
                    app(ApproveAssessmentConfiguration::class)->approveThreshold($record, auth()->user(), $data['approval_notes']);
                    Notification::make()->title('Ambang batas berhasil disetujui.')->success()->send();
                }),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }
}
