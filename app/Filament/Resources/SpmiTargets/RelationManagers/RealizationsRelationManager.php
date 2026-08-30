<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RealizationsRelationManager extends RelationManager
{
    protected static string $relationship = 'realizations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('period_year')->label('Tahun')->disabled(),
            TextInput::make('realization_numeric')->label('Realisasi Numerik')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('period_year')->label('Tahun')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('realization_numeric')->label('Realisasi Numerik')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('status')->label('Status')->searchable()->sortable()->placeholder('—'),
            ])
            ->recordActions([
                ViewAction::make()->label('Lihat'),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
