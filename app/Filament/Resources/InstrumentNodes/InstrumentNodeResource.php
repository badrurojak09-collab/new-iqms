<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstrumentNodes;

use App\Filament\Resources\InstrumentNodes\Pages\CreateInstrumentNode;
use App\Filament\Resources\InstrumentNodes\Pages\EditInstrumentNode;
use App\Filament\Resources\InstrumentNodes\Pages\ListInstrumentNodes;
use App\Models\InstrumentNode;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class InstrumentNodeResource extends Resource
{
    protected static ?string $model = InstrumentNode::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Elemen Instrumen';

    protected static ?string $modelLabel = 'Elemen Instrumen';

    protected static ?string $pluralModelLabel = 'Elemen Instrumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Elemen Instrumen')
                ->description('Susun hierarki kriteria, elemen, dan indikator dalam versi instrumen tertentu.')
                ->icon('heroicon-o-list-bullet')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('version', 'version_label')->searchable()->preload()->required(),
                    Select::make('parent_id')->label('Elemen Induk')->relationship('parent', 'title')->searchable()->preload()->helperText('Kosongkan untuk elemen tingkat teratas.'),
                    Select::make('node_type')->label('Jenis Elemen')->options(['standard' => 'Standar', 'criterion' => 'Kriteria', 'element' => 'Elemen', 'indicator' => 'Indikator'])->required(),
                    TextInput::make('code')->label('Kode Elemen')->required()->maxLength(100),
                    TextInput::make('title')->label('Judul Elemen')->required()->maxLength(255),
                    Textarea::make('requirement')->label('Persyaratan')->rows(4)->columnSpanFull(),
                    Textarea::make('guidance')->label('Panduan Penilaian')->rows(4)->columnSpanFull(),
                    TextInput::make('weight')->label('Bobot')->numeric()->minValue(0),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->integer()->default(0),
                    Toggle::make('is_required')->label('Wajib Dinilai')->default(false),
                    Textarea::make('metadata')->label('Metadata')->helperText('JSON metadata opsional.')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('version.family.name')->label('Keluarga Instrumen')->sortable()->searchable(),
            TextColumn::make('version.version_label')->label('Versi Instrumen')->sortable()->searchable(),
            TextColumn::make('code')->label('Kode Elemen')->searchable()->sortable(),
            TextColumn::make('title')->label('Judul Elemen')->searchable()->wrap(),
            TextColumn::make('node_type')->label('Jenis Elemen')->formatStateUsing(fn (?string $state): string => match ($state) {
                'standard' => 'Standar', 'criterion' => 'Kriteria', 'element' => 'Elemen', 'indicator' => 'Indikator', default => $state ?: '—',
            })->badge(),
            TextColumn::make('weight')->label('Bobot')->numeric(),
            TextColumn::make('is_required')->label('Wajib')->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')->badge(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return ['index' => ListInstrumentNodes::route('/'), 'create' => CreateInstrumentNode::route('/create'), 'edit' => EditInstrumentNode::route('/{record}/edit')];
    }
}
