<?php

declare(strict_types=1);

namespace App\Filament\Resources\LedTemplates;

use App\Filament\Resources\LedTemplates\RelationManagers\LedTemplateSectionsRelationManager;
use App\Models\LedTemplate;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LedTemplateResource extends Resource
{
    protected static ?string $model = LedTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Akreditasi';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Template LED';

    protected static ?string $modelLabel = 'Template LED';

    protected static ?string $pluralModelLabel = 'Template LED';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Template LED')
                ->description('Kelola struktur template Laporan Evaluasi Diri berdasarkan versi instrumen akreditasi.')
                ->icon('heroicon-o-document-text')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Template')->required()->maxLength(100)->alphaDash()->unique(ignoreRecord: true),
                    TextInput::make('name')->label('Nama Template')->required()->maxLength(255),
                    Textarea::make('description')->label('Deskripsi')->rows(4)->columnSpanFull(),
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
            TextColumn::make('sections_count')->counts('sections')->label('Jumlah Bagian')->sortable(),
        ]);
    }

    public static function getRelations(): array
    {
        return [LedTemplateSectionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedTemplates::route('/'),
            'create' => Pages\CreateLedTemplate::route('/create'),
            'edit' => Pages\EditLedTemplate::route('/{record}/edit'),
        ];
    }
}
