<?php declare(strict_types=1);

namespace App\Filament\Resources\AccreditationCriteria;

use App\Models\AccreditationCriterion;
use Filament\Forms\Components\KeyValue;
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
use BackedEnum;

class AccreditationCriterionResource extends Resource
{
    protected static ?string $model = AccreditationCriterion::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Akreditasi';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Kriteria Akreditasi';

    protected static ?string $modelLabel = 'Kriteria Akreditasi';

    protected static ?string $pluralModelLabel = 'Kriteria Akreditasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kriteria Akreditasi')
                ->description('Kelola kriteria penilaian berdasarkan versi instrumen BAN-PT atau LAM yang digunakan.')
                ->icon('heroicon-o-clipboard-document-list')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')
                        ->label('Versi Instrumen')
                        ->relationship('instrumentVersion', 'version_label')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('code')
                        ->label('Kode Kriteria')
                        ->required()
                        ->maxLength(100)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->label('Nama Kriteria')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('sort_order')
                        ->label('Urutan Tampilan')
                        ->numeric()
                        ->integer()
                        ->default(0)
                        ->minValue(0)
                        ->required(),
                    Toggle::make('is_required')
                        ->label('Kriteria Wajib')
                        ->default(true)
                        ->inline(false),
                    Textarea::make('description')
                        ->label('Deskripsi Kriteria')
                        ->rows(4)
                        ->columnSpanFull(),
                    KeyValue::make('metadata')
                        ->label('Metadata Tambahan')
                        ->keyLabel('Nama Atribut')
                        ->valueLabel('Nilai Atribut')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('instrumentVersion.family.name')->label('Keluarga Instrumen')->sortable(),
            TextColumn::make('instrumentVersion.version_label')->label('Versi')->sortable(),
            TextColumn::make('code')->label('Kode Kriteria')->searchable()->sortable(),
            TextColumn::make('name')->label('Nama Kriteria')->searchable()->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
            TextColumn::make('is_required')->label('Wajib')->badge()->formatStateUsing(fn(mixed $state): string => (bool) $state ? 'Ya' : 'Tidak')->color(fn(mixed $state): string => (bool) $state ? 'success' : 'gray'),
            TextColumn::make('mappings_count')->counts('mappings')->label('Pemetaan')->sortable(),
        ])->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccreditationCriteria::route('/'),
            'create' => Pages\CreateAccreditationCriterion::route('/create'),
            'edit' => Pages\EditAccreditationCriterion::route('/{record}/edit')
        ];
    }
}
