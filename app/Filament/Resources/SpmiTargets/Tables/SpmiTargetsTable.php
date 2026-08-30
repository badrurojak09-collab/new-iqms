<?php declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SpmiTargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['indicator', 'perguruanTinggi', 'programStudi']))
            ->columns([
                TextColumn::make('indicator.name')->label('Indikator')->searchable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('—')->searchable(),
                TextColumn::make('period_year')->label('Tahun')->sortable(),
                TextColumn::make('target_numeric')->label('Target Numerik')->placeholder('—'),
                TextColumn::make('status')->label('Status')->badge(),
            ])
            ->filters([
                TrashedFilter::make()->label('Data Terhapus'),
            ])
            ->recordActions([
                RestoreAction::make()->label('Pulihkan')->visible(fn($record): bool => $record->trashed()),
                ForceDeleteAction::make()->label('Hapus Permanen')->visible(fn($record): bool => $record->trashed()),
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
