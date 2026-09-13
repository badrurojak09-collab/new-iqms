<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles;

use App\Filament\Clusters\AmiCluster;
use App\Support\Tenancy\TenantQuery;
use App\Filament\Resources\AmiCycles\Pages\CreateAmiCycle;
use App\Filament\Resources\AmiCycles\Pages\EditAmiCycle;
use App\Filament\Resources\AmiCycles\Pages\ListAmiCycles;
use App\Filament\Resources\AmiCycles\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\AmiCycles\RelationManagers\ChecklistItemsRelationManager;
use App\Filament\Resources\AmiCycles\RelationManagers\FindingsRelationManager;
use App\Filament\Resources\AmiCycles\Schemas\AmiCycleForm;
use App\Filament\Resources\AmiCycles\Tables\AmiCyclesTable;
use App\Models\AmiCycle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class AmiCycleResource extends Resource
{
    protected static ?string $model = AmiCycle::class;
    protected static ?string $cluster = AmiCluster::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::BarsArrowDown;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Siklus AMI';
    protected static ?string $modelLabel = 'Siklus AMI';
    protected static ?string $pluralModelLabel = 'Siklus AMI';

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forOptionalProgramStudi(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return AmiCycleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiCyclesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [AssignmentsRelationManager::class, ChecklistItemsRelationManager::class, FindingsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmiCycles::route('/'),
            'create' => CreateAmiCycle::route('/create'),
            'edit' => EditAmiCycle::route('/{record}/edit'),
        ];
    }
}
