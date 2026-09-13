<?php

declare(strict_types=1);

namespace App\Filament\Resources\PerguruanTinggiStandards;

use App\Filament\Clusters\SpmiCluster;
use App\Filament\Resources\PerguruanTinggiStandards\Pages\CreatePerguruanTinggiStandard;
use App\Filament\Resources\PerguruanTinggiStandards\Pages\EditPerguruanTinggiStandard;
use App\Filament\Resources\PerguruanTinggiStandards\Pages\ListPerguruanTinggiStandards;
use App\Filament\Resources\PerguruanTinggiStandards\RelationManagers\SpmiStandardsRelationManager;
use App\Filament\Resources\PerguruanTinggiStandards\Schemas\PerguruanTinggiStandardForm;
use App\Filament\Resources\PerguruanTinggiStandards\Tables\PerguruanTinggiStandardsTable;
use App\Models\PerguruanTinggiStandard;
use App\Support\Tenancy\TenantQuery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class PerguruanTinggiStandardResource extends Resource
{
    protected static ?string $model = PerguruanTinggiStandard::class;
    protected static ?string $cluster = SpmiCluster::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::BookOpen;
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Standar Perguruan Tinggi';
    protected static ?string $modelLabel = 'Standar Perguruan Tinggi';
    protected static ?string $pluralModelLabel = 'Standar Perguruan Tinggi';

    public static function form(Schema $schema): Schema
    {
        return PerguruanTinggiStandardForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerguruanTinggiStandardsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [SpmiStandardsRelationManager::class];
    }

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forPerguruanTinggi(parent::getEloquentQuery(), auth()->user());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerguruanTinggiStandards::route('/'),
            'create' => CreatePerguruanTinggiStandard::route('/create'),
            'edit' => EditPerguruanTinggiStandard::route('/{record}/edit'),
        ];
    }
}
