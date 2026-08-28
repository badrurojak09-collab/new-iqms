<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiStandards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpmiStandardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Standar')->searchable()->sortable(),
                TextColumn::make('framework.name')->label('Framework')->searchable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable(),
                TextColumn::make('indicators_count')->counts('indicators')->label('Jumlah Indikator')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => match ($state) { 'draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan', default => (string) $state, })->sortable(),
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
