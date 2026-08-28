<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationDecisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'decisions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('decision_type')->options(['internal' => 'Internal', 'external' => 'External'])->required()->default('internal'),
            TextInput::make('result')->required()->maxLength(50),
            DatePicker::make('decision_date')->required()->default(now()),
            DatePicker::make('valid_until'),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('decision_type')->label('Jenis Keputusan')->badge()->formatStateUsing(fn (mixed $state): string => $state === 'external' ? 'Eksternal' : 'Internal'),
            TextColumn::make('result')->label('Hasil Keputusan')->searchable()->sortable(),
            TextColumn::make('decision_date')->label('Tanggal Keputusan')->date()->sortable(),
            TextColumn::make('valid_until')->label('Berlaku Sampai')->date()->sortable(),
        ])->defaultSort('decision_date', 'desc');
    }
}
