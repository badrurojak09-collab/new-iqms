<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations;

use App\Filament\Resources\SpmiRealizations\Pages\CreateSpmiRealization;
use App\Filament\Resources\SpmiRealizations\Pages\EditSpmiRealization;
use App\Filament\Resources\SpmiRealizations\Pages\ListSpmiRealizations;
use App\Filament\Resources\SpmiRealizations\Schemas\SpmiRealizationForm;
use App\Filament\Resources\SpmiRealizations\Tables\SpmiRealizationsTable;
use App\Models\SpmiRealization;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpmiRealizationResource extends Resource
{
    protected static ?string $model = SpmiRealization::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'SPMI';
    protected static ?int $navigationSort = 50;
    protected static ?string $navigationLabel = 'Realisasi SPMI';
    protected static ?string $modelLabel = 'Realisasi SPMI';
    protected static ?string $pluralModelLabel = 'Realisasi SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiRealizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiRealizationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiRealizations::route('/'),
            'create' => CreateSpmiRealization::route('/create'),
            'edit' => EditSpmiRealization::route('/{record}/edit'),
        ];
    }
}
