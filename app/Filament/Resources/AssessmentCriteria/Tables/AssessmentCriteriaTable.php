<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentCriteria\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentCriteriaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
                TextColumn::make('code')->label('Kode Kriteria')->sortable()->searchable()->copyable(),
                TextColumn::make('name')->label('Nama Kriteria')->wrap()->searchable(),
                TextColumn::make('instrumentNode.title')->label('Elemen Instrumen')->wrap()->limit(40),
                TextColumn::make('weight')->label('Bobot')->suffix('%')->sortable(),
                TextColumn::make('minimum_score')->label('Skor Minimum')->placeholder('—')->sortable(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                TextColumn::make('is_required')->label('Wajib')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
            ])
            ->filters([
                SelectFilter::make('is_required')->label('Kriteria Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib']),
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
