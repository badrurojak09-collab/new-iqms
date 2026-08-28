<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentScales\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'options';

    protected static ?string $title = 'Opsi Skala';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Opsi Skala Penilaian')
                ->description('Tentukan pilihan jawaban, nilai numerik, dan urutan tampil pada skala kustom.')
                ->icon('heroicon-o-list-bullet')
                ->columns(2)
                ->schema([
                    TextInput::make('code')->label('Kode Opsi')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('label')->label('Label Opsi')->required()->maxLength(255),
                    TextInput::make('numeric_value')->label('Nilai Numerik')->numeric(),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->default(0)->required(),
                    Textarea::make('metadata')->label('Metadata')->helperText('JSON metadata opsional.')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode Opsi')->searchable()->sortable()->copyable(),
                TextColumn::make('label')->label('Label Opsi')->searchable()->wrap(),
                TextColumn::make('numeric_value')->label('Nilai Numerik')->sortable()->placeholder('—'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Opsi'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([]);
    }
}
