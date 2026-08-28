<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks\Tables;

use App\Models\SpmiFramework;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SpmiFrameworksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Framework')->searchable()->sortable(),
                TextColumn::make('version_label')->label('Versi')->placeholder('—')->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('standards_count')->counts('standards')->label('Jumlah Standar')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => match ($state) {
                    'draft' => 'Draf',
                    'active' => 'Aktif',
                    'archived' => 'Diarsipkan',
                    default => (string) $state,
                })->sortable(),
                TextColumn::make('effective_from')->label('Mulai Berlaku')->date('d M Y')->placeholder('—')->sortable(),
                TextColumn::make('effective_until')->label('Berakhir')->date('d M Y')->placeholder('—')->sortable(),
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
                    DeleteBulkAction::make()->label('Hapus yang dipilih'),
                    ForceDeleteBulkAction::make()->label('Hapus permanen'),
                    RestoreBulkAction::make()->label('Pulihkan'),
                ]),
            ]);
    }
}

