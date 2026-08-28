<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AmiChecklistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('cycle.code')->label('Siklus AMI')->searchable()->sortable(),
                TextColumn::make('cycle.perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('cycle.programStudi.nama_prodi')->label('Program Studi')->placeholder('Tingkat Perguruan Tinggi')->searchable(),
                TextColumn::make('question')->label('Pertanyaan Audit')->wrap()->limit(100)->searchable(),
                TextColumn::make('response_type')->label('Jenis Respons')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'boolean' => 'Ya/Tidak',
                    'numeric' => 'Numerik',
                    'choice' => 'Pilihan',
                    default => 'Teks',
                })->badge(),
                TextColumn::make('response_status')->label('Status Respons')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'in_progress' => 'Sedang Dikerjakan',
                    'completed' => 'Selesai',
                    'verified' => 'Terverifikasi',
                    default => 'Belum Dimulai',
                })->badge(),
                TextColumn::make('score')->label('Skor')->placeholder('—')->sortable(),
                TextColumn::make('evidence_required')->label('Evidence')->formatStateUsing(fn ($state): string => $state ? 'Wajib' : 'Tidak Wajib')->badge(),
            ])
            ->filters([
                SelectFilter::make('response_status')->label('Status Respons')->options(['not_started' => 'Belum Dimulai', 'in_progress' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi']),
                SelectFilter::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'boolean' => 'Ya/Tidak', 'numeric' => 'Numerik', 'choice' => 'Pilihan']),
                SelectFilter::make('evidence_required')->label('Evidence')->options([1 => 'Wajib', 0 => 'Tidak Wajib']),
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
