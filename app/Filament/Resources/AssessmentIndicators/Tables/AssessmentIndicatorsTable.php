<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentIndicators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentIndicatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('element.title')->label('Elemen Penilaian')->wrap()->searchable(),
                TextColumn::make('code')->label('Kode Indikator')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('Nama Indikator')->wrap()->searchable()->sortable(),
                TextColumn::make('unit')->label('Satuan')->placeholder('—'),
                TextColumn::make('direction')->label('Arah Evaluasi')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'higher_is_better' => 'Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target', default => $state ?: '—',
                })->badge(),
                TextColumn::make('data_type')->label('Tipe Data')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'integer' => 'Bilangan Bulat', 'decimal' => 'Desimal', 'percentage' => 'Persentase', 'currency' => 'Mata Uang', 'text' => 'Teks', 'boolean' => 'Ya/Tidak', 'date' => 'Tanggal', default => $state ?: '—',
                })->badge(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                TextColumn::make('is_required')->label('Wajib')->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')->badge(),
            ])
            ->filters([
                SelectFilter::make('direction')->label('Arah Evaluasi')->options(['higher_is_better' => 'Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target']),
                SelectFilter::make('is_required')->label('Indikator Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib']),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ]);
    }
}
