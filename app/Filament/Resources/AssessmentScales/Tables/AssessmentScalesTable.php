<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentScales\Tables;

use App\Support\Ui\StatusLabel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssessmentScalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode Skala')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('Nama Skala')->searchable()->sortable(),
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->searchable()->sortable(),
                TextColumn::make('scale_type')->label('Jenis Skala')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'numeric' => 'Numerik',
                    'ordinal' => 'Ordinal',
                    'binary' => 'Biner',
                    'percentage' => 'Persentase',
                    'custom' => 'Kustom',
                    default => $state ?: '—',
                })->badge()->sortable(),
                TextColumn::make('min_value')->label('Minimum')->sortable(),
                TextColumn::make('max_value')->label('Maksimum')->sortable(),
                TextColumn::make('precision')->label('Desimal')->sortable(),
                TextColumn::make('options_count')->counts('options')->label('Jumlah Opsi')->sortable(),
            ])
            ->filters([
                SelectFilter::make('scale_type')->label('Jenis Skala')->options(['numeric' => 'Numerik', 'ordinal' => 'Ordinal', 'binary' => 'Biner', 'percentage' => 'Persentase', 'custom' => 'Kustom']),
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
