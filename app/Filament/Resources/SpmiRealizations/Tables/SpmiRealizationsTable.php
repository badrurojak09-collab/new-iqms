<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpmiRealizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('indicator.name')->label('Indikator')->searchable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('—')->searchable(),
                TextColumn::make('period_year')->label('Tahun')->sortable(),
                TextColumn::make('realization_numeric')->label('Realisasi Numerik')->placeholder('—'),
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
