<?php

declare(strict_types=1);

namespace App\Filament\Resources\LkpsTemplates;

use App\Filament\Resources\LkpsTemplates\RelationManagers\LkpsTemplateColumnsRelationManager;
use App\Models\LkpsTemplate;
use BackedEnum;
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

class LkpsTemplateResource extends Resource
{
    protected static ?string $model = LkpsTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Akreditasi';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Template LKPS';

    protected static ?string $modelLabel = 'Template LKPS';

    protected static ?string $pluralModelLabel = 'Template LKPS';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Template LKPS')
                ->description('Kelola struktur tabel Laporan Kinerja Program Studi berdasarkan versi instrumen akreditasi.')
                ->icon('heroicon-o-table-cells')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Template')->required()->maxLength(100)->alphaDash()->unique(ignoreRecord: true),
                    TextInput::make('name')->label('Nama Template')->required()->maxLength(255),
                    Textarea::make('description')->label('Deskripsi')->rows(4)->columnSpanFull(),
                    Toggle::make('is_required')->label('Template Wajib')->default(false),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->integer()->default(0),
                    KeyValue::make('row_definition')->label('Definisi Baris')->keyLabel('Kunci')->valueLabel('Nilai')->columnSpanFull(),
                    KeyValue::make('validation_rules')->label('Aturan Validasi')->keyLabel('Aturan')->valueLabel('Nilai')->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('instrumentVersion.family.name')->label('Keluarga Instrumen')->sortable()->searchable(),
            TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
            TextColumn::make('code')->label('Kode Template')->searchable()->sortable()->copyable(),
            TextColumn::make('name')->label('Nama Template')->searchable()->sortable(),
            TextColumn::make('columns_count')->counts('columns')->label('Jumlah Kolom')->sortable(),
            TextColumn::make('is_required')->label('Wajib')->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak')->badge(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->defaultSort('sort_order');
    }

    public static function getRelations(): array
    {
        return [LkpsTemplateColumnsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLkpsTemplates::route('/'),
            'create' => Pages\CreateLkpsTemplate::route('/create'),
            'edit' => Pages\EditLkpsTemplate::route('/{record}/edit'),
        ];
    }
}
