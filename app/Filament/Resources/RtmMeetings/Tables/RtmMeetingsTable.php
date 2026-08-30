<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RtmMeetingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['perguruanTinggi', 'programStudi', 'amiCycle']))
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('title')->label('Judul Rapat')->searchable()->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('Tingkat Perguruan Tinggi')->searchable(),
                TextColumn::make('amiCycle.code')->label('Siklus AMI')->placeholder('—')->searchable(),
                TextColumn::make('held_at')->label('Waktu Pelaksanaan')->dateTime()->placeholder('Belum ditentukan')->sortable(),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default => 'Direncanakan',
                })->badge(),
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
