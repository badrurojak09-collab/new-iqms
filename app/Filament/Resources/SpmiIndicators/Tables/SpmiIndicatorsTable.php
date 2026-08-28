<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpmiIndicatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Indikator')->searchable()->sortable(),
                TextColumn::make('standard.name')->label('Standar')->searchable(),
                TextColumn::make('measurement_type')->label('Pengukuran')->badge(),
                TextColumn::make('unit')->label('Satuan')->placeholder('—'),
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
