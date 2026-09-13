<?php

declare(strict_types=1);

namespace App\Filament\Resources\PerguruanTinggiStandards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

final class PerguruanTinggiStandardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Standar')->searchable()->sortable()->wrap(),
                TextColumn::make('category')->label('Kategori')->badge()->formatStateUsing(fn(?string $state): string => match ($state) {
                    'academic' => 'Akademik',
                    'non_academic' => 'Non-Akademik',
                    'service' => 'Pelayanan Minimal',
                    'procedure' => 'SOP/Prosedur',
                    default => (string) $state,
                }),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('spmi_standards_count')->counts('spmiStandards')->label('Standar SPMI')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(?string $state): string => match ($state) {
                    'draft' => 'Draf',
                    'active' => 'Aktif',
                    'archived' => 'Diarsipkan',
                    default => (string) $state,
                }),
                TextColumn::make('effective_from')->label('Mulai Berlaku')->date('d M Y')->placeholder('—'),
                TextColumn::make('effective_until')->label('Berakhir')->date('d M Y')->placeholder('—'),
                TextColumn::make('deleted_at')->label('Dihapus')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->label('Kategori')->options(['academic' => 'Akademik', 'non_academic' => 'Non-Akademik', 'service' => 'Pelayanan Minimal', 'procedure' => 'SOP/Prosedur']),
                SelectFilter::make('status')->label('Status')->options(['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan']),
                TrashedFilter::make()->label('Data Terhapus'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
                RestoreAction::make()->label('Pulihkan'),
                ForceDeleteAction::make()->label('Hapus Permanen'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()->label('Hapus yang dipilih'),
                ]),
            ]);
    }
}
