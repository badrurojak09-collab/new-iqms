<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;

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
            ->filters([TrashedFilter::make()->label('Data Terhapus')])
            ->recordActions([
                \Filament\Actions\Action::make('exportMinutes')
                    ->label('Cetak Risalah')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn ($record): string => route('rtm-meetings.export-minutes', ['meeting' => $record->getKey()]))
                    ->openUrlInNewTab(),
                RestoreAction::make()->label('Pulihkan')->visible(fn ($record): bool => $record->trashed()),
                ForceDeleteAction::make()->label('Hapus Permanen')->visible(fn ($record): bool => $record->trashed()),
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
