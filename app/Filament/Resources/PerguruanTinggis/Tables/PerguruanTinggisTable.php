<?php

namespace App\Filament\Resources\PerguruanTinggis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PerguruanTinggisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_pt')->label('Kode PT')->searchable()->sortable(),
                TextColumn::make('nama_pt')->label('Nama Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('yayasan.nama')->label('Yayasan')->searchable()->sortable(),
                TextColumn::make('jenis')->label('Jenis')->badge()->formatStateUsing(fn (?string $state): string => match ($state) {
                    'universitas' => 'Universitas',
                    'institut' => 'Institut',
                    'sekolah_tinggi' => 'Sekolah Tinggi',
                    'politeknik' => 'Politeknik',
                    'akademi' => 'Akademi',
                    default => (string) $state,
                }),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => match ($state) {
                    'active' => 'Aktif',
                    'inactive' => 'Tidak Aktif',
                    default => (string) $state,
                }),
                TextColumn::make('deleted_at')->label('Dihapus')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
