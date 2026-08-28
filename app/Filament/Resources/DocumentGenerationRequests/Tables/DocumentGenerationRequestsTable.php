<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentGenerationRequests\Tables;

use App\Models\DocumentGenerationRequest;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class DocumentGenerationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['definition', 'perguruanTinggi', 'programStudi'])->withCount(['snapshots', 'artifacts']))
            ->columns([
                TextColumn::make('definition.name')->label('Jenis Dokumen')->searchable()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (?string $state): string => match ($state) { 'queued' => 'Menunggu', 'processing' => 'Diproses', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', default => (string) $state, })->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->searchable()->sortable(),
                TextColumn::make('period_label')->label('Periode')->sortable(),
                TextColumn::make('snapshots_count')->label('Snapshot')->sortable(),
                TextColumn::make('artifacts_count')->label('Artifact')->sortable(),
                TextColumn::make('completed_at')->label('Selesai Pada')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview HTML')
                    ->url(fn (DocumentGenerationRequest $record): string => route('document-output.preview', ['request' => $record]))
                    ->openUrlInNewTab()
                    ->visible(fn (DocumentGenerationRequest $record): bool => $record->status === 'completed' && $record->artifacts()->where('format', 'html')->exists()),
                Action::make('download')
                    ->label('Unduh Laporan')
                    ->url(fn (DocumentGenerationRequest $record): string => route('document-output.download', ['request' => $record]))
                    ->visible(fn (DocumentGenerationRequest $record): bool => $record->status === 'completed' && $record->artifacts()->exists()),
            ])
            ->toolbarActions([]);
    }
}
