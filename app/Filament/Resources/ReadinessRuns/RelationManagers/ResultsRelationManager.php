<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReadinessRuns\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('item_key')->label('Kunci Item')->searchable()->sortable(),
            TextColumn::make('assessmentElement.code')->label('Elemen Penilaian')->placeholder('Respons Cadangan')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->sortable(),
            TextColumn::make('completion_percent')->label('Kelengkapan')->suffix('%')->sortable(),
            TextColumn::make('evidence_percent')->label('Kelengkapan Evidence')->suffix('%')->sortable(),
            TextColumn::make('score')->label('Skor')->sortable(),
            TextColumn::make('gap_count')->label('Jumlah Gap')->sortable(),
            TextColumn::make('mapping_results_count')->label('Pemetaan')->counts('mappingResults')->sortable(),
        ])->headerActions([])->recordActions([])->bulkActions([]);
    }
}
