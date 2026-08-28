<?php

declare(strict_types=1);

namespace App\Filament\Resources\Evidences\RelationManagers;

use App\Support\Ui\StatusLabel;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvidenceLinkChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'linkChecks';

    protected static ?string $title = 'Riwayat Pemeriksaan Tautan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->label('Status Tautan')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('http_status')->label('Status HTTP')->sortable()->placeholder('—'),
                TextColumn::make('url_hash')->label('Hash URL')->limit(20)->copyable(),
                TextColumn::make('checker.name')->label('Diperiksa Oleh')->sortable()->placeholder('Sistem'),
                TextColumn::make('checked_at')->label('Diperiksa Pada')->dateTime()->sortable(),
                TextColumn::make('notes')->label('Catatan Pemeriksaan')->wrap()->limit(60),
            ])
            ->defaultSort('checked_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
