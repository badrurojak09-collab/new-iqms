<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators;

use App\Filament\Resources\SpmiIndicators\Pages\CreateSpmiIndicator;
use App\Filament\Resources\SpmiIndicators\Pages\EditSpmiIndicator;
use App\Filament\Resources\SpmiIndicators\Pages\ListSpmiIndicators;
use App\Filament\Resources\SpmiIndicators\Schemas\SpmiIndicatorForm;
use App\Filament\Resources\SpmiIndicators\Tables\SpmiIndicatorsTable;
use App\Models\SpmiIndicator;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpmiIndicatorResource extends Resource
{
    protected static ?string $model = SpmiIndicator::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'SPMI';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Indikator SPMI';
    protected static ?string $modelLabel = 'Indikator SPMI';
    protected static ?string $pluralModelLabel = 'Indikator SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiIndicatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiIndicatorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiIndicators::route('/'),
            'create' => CreateSpmiIndicator::route('/create'),
            'edit' => EditSpmiIndicator::route('/{record}/edit'),
        ];
    }
}
