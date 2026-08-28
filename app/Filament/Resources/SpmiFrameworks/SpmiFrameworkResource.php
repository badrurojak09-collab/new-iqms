<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks;

use App\Filament\Resources\SpmiFrameworks\Pages\CreateSpmiFramework;
use App\Filament\Resources\SpmiFrameworks\Pages\EditSpmiFramework;
use App\Filament\Resources\SpmiFrameworks\Pages\ListSpmiFrameworks;
use App\Filament\Resources\SpmiFrameworks\Schemas\SpmiFrameworkForm;
use App\Filament\Resources\SpmiFrameworks\Tables\SpmiFrameworksTable;
use App\Models\SpmiFramework;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpmiFrameworkResource extends Resource
{
    protected static ?string $model = SpmiFramework::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Framework SPMI';

    protected static ?string $modelLabel = 'Framework SPMI';

    protected static ?string $pluralModelLabel = 'Framework SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiFrameworkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiFrameworksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiFrameworks::route('/'),
            'create' => CreateSpmiFramework::route('/create'),
            'edit' => EditSpmiFramework::route('/{record}/edit'),
        ];
    }
}

