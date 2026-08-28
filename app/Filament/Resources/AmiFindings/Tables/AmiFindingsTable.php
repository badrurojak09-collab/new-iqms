<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AmiFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode Temuan')->searchable()->sortable(),
                TextColumn::make('cycle.code')->label('Siklus AMI')->searchable()->sortable(),
                TextColumn::make('cycle.perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('cycle.programStudi.nama_prodi')->label('Program Studi')->placeholder('Tingkat Perguruan Tinggi')->searchable(),
                TextColumn::make('classification')->label('Klasifikasi')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'nonconformity' => 'Ketidaksesuaian',
                    'opportunity' => 'Peluang Perbaikan',
                    default => 'Observasi',
                })->badge(),
                TextColumn::make('severity')->label('Keparahan')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'major' => 'Mayor',
                    'minor' => 'Minor',
                    'low' => 'Rendah',
                    default => 'Sedang',
                })->badge(),
                TextColumn::make('condition')->label('Kondisi')->wrap()->limit(90),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'in_progress' => 'Dalam Tindak Lanjut',
                    'closed' => 'Ditutup',
                    default => 'Terbuka',
                })->badge(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('classification')->label('Klasifikasi')->options(['observation' => 'Observasi', 'nonconformity' => 'Ketidaksesuaian', 'opportunity' => 'Peluang Perbaikan']),
                SelectFilter::make('severity')->label('Keparahan')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'minor' => 'Minor', 'major' => 'Mayor']),
                SelectFilter::make('status')->label('Status')->options(['open' => 'Terbuka', 'in_progress' => 'Dalam Tindak Lanjut', 'closed' => 'Ditutup']),
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
