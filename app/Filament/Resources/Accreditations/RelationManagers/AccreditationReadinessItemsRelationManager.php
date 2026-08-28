<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationReadinessItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'readinessItems';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('item_type')->options(['led' => 'LED', 'lkps' => 'LKPS', 'evidence' => 'Evidence', 'mapping' => 'Pemetaan'])->required(),
            TextInput::make('item_key')->required()->maxLength(120),
            Select::make('status')->options(['pending' => 'Menunggu', 'in_progress' => 'In Progress', 'done' => 'Done', 'blocked' => 'Blocked'])->required()->default('pending'),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('item_type')->label('Jenis Item')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'led' => 'LED',
                'lkps' => 'LKPS',
                'evidence' => 'Evidence',
                'mapping' => 'Pemetaan',
                default => (string) $state,
            }),
            TextColumn::make('item_key')->label('Kunci Item')->searchable()->sortable(),
            TextColumn::make('status')->label('Status Kesiapan')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('checked_at')->label('Diperiksa Pada')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc');
    }
}
