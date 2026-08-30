<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TargetsRelationManager extends RelationManager
{
    protected static string $relationship = 'targets';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('period_code')->label('Periode')->disabled(),
            TextInput::make('period_year')->label('Tahun')->disabled(),
            TextInput::make('target_numeric')->label('Target Numerik')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('period_code')->label('Periode')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('period_year')->label('Tahun')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('target_numeric')->label('Target Numerik')->searchable()->sortable()->placeholder('—'),
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
