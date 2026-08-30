<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('result')->label('Hasil')->disabled(),
            TextInput::make('achievement_percentage')->label('Persentase Ketercapaian')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
            TextInput::make('evaluated_at')->label('Dievaluasi Pada')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('result')->label('Hasil')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('achievement_percentage')->label('Persentase Ketercapaian')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('status')->label('Status')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('evaluated_at')->label('Dievaluasi Pada')->searchable()->sortable()->placeholder('—'),
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
