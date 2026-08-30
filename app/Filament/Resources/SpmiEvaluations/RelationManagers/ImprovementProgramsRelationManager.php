<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImprovementProgramsRelationManager extends RelationManager
{
    protected static string $relationship = 'improvementPrograms';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode')->disabled(),
            TextInput::make('title')->label('Judul Program')->disabled(),
            TextInput::make('progress_percent')->label('Kemajuan')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
            TextInput::make('due_date')->label('Batas Waktu')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('title')->label('Judul Program')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('progress_percent')->label('Kemajuan')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('status')->label('Status')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('due_date')->label('Batas Waktu')->searchable()->sortable()->placeholder('—'),
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
