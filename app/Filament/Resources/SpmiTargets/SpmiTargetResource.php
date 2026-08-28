<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets;

use App\Filament\Resources\SpmiTargets\Pages\CreateSpmiTarget;
use App\Filament\Resources\SpmiTargets\Pages\EditSpmiTarget;
use App\Filament\Resources\SpmiTargets\Pages\ListSpmiTargets;
use App\Filament\Resources\SpmiTargets\Schemas\SpmiTargetForm;
use App\Filament\Resources\SpmiTargets\Tables\SpmiTargetsTable;
use App\Models\SpmiTarget;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SpmiTargetResource extends Resource
{
    protected static ?string $model = SpmiTarget::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'SPMI';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Target SPMI';
    protected static ?string $modelLabel = 'Target SPMI';
    protected static ?string $pluralModelLabel = 'Target SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiTargetsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiTargets::route('/'),
            'create' => CreateSpmiTarget::route('/create'),
            'edit' => EditSpmiTarget::route('/{record}/edit'),
        ];
    }
}
