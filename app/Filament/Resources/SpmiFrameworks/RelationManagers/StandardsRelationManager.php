<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StandardsRelationManager extends RelationManager
{
    protected static string $relationship = 'standards';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode')->disabled(),
            TextInput::make('name')->label('Nama Standar')->disabled(),
            TextInput::make('programStudi.nama_prodi')->label('Program Studi')->disabled(),
            TextInput::make('status')->label('Status')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('name')->label('Nama Standar')->searchable()->sortable()->placeholder('—'),
            TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->searchable()->sortable()->placeholder('—'),
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
