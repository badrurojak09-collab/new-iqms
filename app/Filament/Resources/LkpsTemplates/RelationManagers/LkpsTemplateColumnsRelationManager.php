<?php

declare(strict_types=1);

namespace App\Filament\Resources\LkpsTemplates\RelationManagers;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LkpsTemplateColumnsRelationManager extends RelationManager
{
    protected static string $relationship = 'columns';

    protected static ?string $title = 'Kolom LKPS';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kolom LKPS')
                ->description('Definisikan kolom, tipe data, batas nilai, dan sumber pengisian tabel LKPS.')
                ->icon('heroicon-o-table-cells')
                ->columns(2)
                ->schema([
                    TextInput::make('column_key')->label('Kunci Kolom')->required()->maxLength(100),
                    TextInput::make('label')->label('Label Kolom')->required()->maxLength(255),
                    Select::make('data_type')->label('Tipe Data')->options(['string' => 'Teks', 'integer' => 'Bilangan Bulat', 'decimal' => 'Desimal', 'date' => 'Tanggal', 'boolean' => 'Ya/Tidak', 'enum' => 'Pilihan'])->required(),
                    TextInput::make('unit')->label('Satuan')->maxLength(50),
                    Toggle::make('is_required')->label('Kolom Wajib')->default(false),
                    TextInput::make('min_value')->label('Nilai Minimum')->numeric(),
                    TextInput::make('max_value')->label('Nilai Maksimum')->numeric()->gte('min_value'),
                    TextInput::make('decimal_scale')->label('Jumlah Desimal')->numeric()->integer()->minValue(0)->maxValue(6),
                    TextInput::make('source_type')->label('Jenis Sumber')->maxLength(50),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->integer()->default(0),
                    KeyValue::make('allowed_values')->label('Nilai yang Diizinkan')->keyLabel('Nilai')->valueLabel('Label')->columnSpanFull(),
                    KeyValue::make('formula')->label('Formula')->keyLabel('Kunci')->valueLabel('Nilai')->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
            TextColumn::make('column_key')->label('Kunci Kolom')->searchable()->sortable(),
            TextColumn::make('label')->label('Label Kolom')->searchable()->wrap(),
            TextColumn::make('data_type')->label('Tipe Data')->formatStateUsing(fn (?string $state): string => match ($state) {
                'string' => 'Teks', 'integer' => 'Bilangan Bulat', 'decimal' => 'Desimal', 'date' => 'Tanggal', 'boolean' => 'Ya/Tidak', 'enum' => 'Pilihan', default => $state ?: '—',
            })->badge(),
            TextColumn::make('unit')->label('Satuan')->placeholder('—'),
            TextColumn::make('is_required')->label('Wajib')->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')->badge(),
            TextColumn::make('source_type')->label('Jenis Sumber')->placeholder('—'),
        ])->defaultSort('sort_order')->reorderable('sort_order');
    }
}
