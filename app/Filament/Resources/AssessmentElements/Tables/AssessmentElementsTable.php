<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentElements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssessmentElementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['criterion.instrumentVersion', 'instrumentNode']))
            ->columns([
                TextColumn::make('criterion.instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
                TextColumn::make('criterion.name')->label('Kriteria Penilaian')->wrap()->searchable(),
                TextColumn::make('instrumentNode.title')->label('Elemen Instrumen')->wrap()->searchable(),
                TextColumn::make('code')->label('Kode Elemen')->searchable()->sortable()->copyable(),
                TextColumn::make('title')->label('Judul Elemen')->searchable()->wrap(),
                TextColumn::make('element_type')->label('Jenis Elemen')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'qualitative' => 'Kualitatif', 'quantitative' => 'Kuantitatif', 'documentary' => 'Dokumenter', 'mixed' => 'Campuran', default => $state ?: '—',
                })->badge(),
                TextColumn::make('weight')->label('Bobot')->numeric()->sortable(),
                TextColumn::make('is_required')->label('Wajib')->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')->badge(),
                TextColumn::make('sort_order')->label('Urutan')->numeric()->sortable(),
                TextColumn::make('created_at')->label('Dibuat Pada')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
