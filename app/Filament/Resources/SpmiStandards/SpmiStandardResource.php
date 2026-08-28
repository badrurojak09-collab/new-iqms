<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiStandards;

use App\Filament\Resources\SpmiStandards\Pages\CreateSpmiStandard;
use App\Filament\Resources\SpmiStandards\Pages\EditSpmiStandard;
use App\Filament\Resources\SpmiStandards\Pages\ListSpmiStandards;
use App\Filament\Resources\SpmiStandards\Schemas\SpmiStandardForm;
use App\Filament\Resources\SpmiStandards\Tables\SpmiStandardsTable;
use App\Models\SpmiStandard;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpmiStandardResource extends Resource
{
    protected static ?string $model = SpmiStandard::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'SPMI';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Standar SPMI';
    protected static ?string $modelLabel = 'Standar SPMI';
    protected static ?string $pluralModelLabel = 'Standar SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiStandardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiStandardsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiStandards::route('/'),
            'create' => CreateSpmiStandard::route('/create'),
            'edit' => EditSpmiStandard::route('/{record}/edit'),
        ];
    }
}
