<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReadinessRuns\Tables;

use App\Support\Ui\StatusLabel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReadinessRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('accreditation.title')->label('Kegiatan Akreditasi')->searchable()->sortable(),
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
                TextColumn::make('status')->label('Status Perhitungan')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('total_items')->label('Total Item')->sortable(),
                TextColumn::make('ready_items')->label('Item Siap')->sortable(),
                TextColumn::make('completion_percent')->label('Penyelesaian')->suffix('%')->sortable(),
                TextColumn::make('weighted_score')->label('Skor Berbobot')->sortable(),
                TextColumn::make('gaps_count')->counts('gaps')->label('Jumlah Gap')->sortable(),
                TextColumn::make('completed_at')->label('Selesai Pada')->dateTime()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status Perhitungan')->options(['pending' => 'Menunggu', 'running' => 'Berjalan', 'completed' => 'Selesai', 'failed' => 'Gagal']),
            ])
            ->recordActions([
                EditAction::make()->label('Lihat Detail'),
            ])
            ->toolbarActions([]);
    }
}
