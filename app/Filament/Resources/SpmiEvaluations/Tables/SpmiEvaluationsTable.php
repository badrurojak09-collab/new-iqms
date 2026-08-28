<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpmiEvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('realization.indicator.name')
                    ->label('Indikator Realisasi')
                    ->description(fn ($record): string => sprintf(
                        'Tahun %s • Nilai: %s',
                        $record->realization?->period_year ?? '—',
                        $record->realization?->realization_numeric ?? ($record->realization?->realization_text ?: '—'),
                    ))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('—')->searchable(),
                TextColumn::make('result')
                    ->label('Hasil Evaluasi')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'met' => 'Tercapai',
                        'partially_met' => 'Tercapai Sebagian',
                        'not_met' => 'Belum Tercapai',
                        default => $state ?: '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'met' => 'success',
                        'partially_met' => 'warning',
                        'not_met' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('achievement_percentage')->label('Ketercapaian')->suffix('%')->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
