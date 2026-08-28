<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->required()->maxLength(100),
            TextInput::make('title')->required()->maxLength(255),
            Select::make('section_type')->options(['led' => 'LED', 'lkps' => 'LKPS', 'other' => 'Other'])->required()->default('led'),
            TextInput::make('sort_order')->numeric()->integer()->default(0),
            Select::make('status')->options(['draft' => 'Draf', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'review' => 'Tinjau'])->required()->default('draft'),
            TextInput::make('readiness_percent')->numeric()->minValue(0)->maxValue(100)->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sort_order')->label('#')->sortable(),
            TextColumn::make('code')->label('Kode Bagian')->searchable()->sortable(),
            TextColumn::make('title')->label('Judul Bagian')->wrap()->searchable(),
            TextColumn::make('section_type')->label('Jenis Bagian')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'led' => 'LED',
                'lkps' => 'LKPS',
                'other' => 'Lainnya',
                default => (string) $state,
            }),
            TextColumn::make('status')->label('Status Bagian')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('readiness_percent')->label('Kesiapan')->suffix('%')->sortable(),
        ])->defaultSort('sort_order')->reorderable('sort_order');
    }
}
