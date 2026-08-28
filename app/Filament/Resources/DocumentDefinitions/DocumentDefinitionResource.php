<?php

namespace App\Filament\Resources\DocumentDefinitions;

use App\Filament\Resources\DocumentDefinitions\Pages\CreateDocumentDefinition;
use App\Filament\Resources\DocumentDefinitions\Pages\EditDocumentDefinition;
use App\Filament\Resources\DocumentDefinitions\Pages\ListDocumentDefinitions;
use App\Filament\Resources\DocumentDefinitions\Schemas\DocumentDefinitionForm;
use App\Filament\Resources\DocumentDefinitions\Tables\DocumentDefinitionsTable;
use App\Models\DocumentDefinition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DocumentDefinitionResource extends Resource
{
    protected static ?string $model = DocumentDefinition::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Reporting';
    protected static ?string $navigationLabel = 'Definisi Output Dokumen';
    protected static ?string $modelLabel = 'definisi output dokumen';
    protected static ?string $pluralModelLabel = 'definisi output dokumen';
    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = null;

    public static function form(Schema $schema): Schema
    {
        return DocumentDefinitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentDefinitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentDefinitions::route('/'),
            'create' => CreateDocumentDefinition::route('/create'),
            'edit' => EditDocumentDefinition::route('/{record}/edit'),
        ];
    }
}
