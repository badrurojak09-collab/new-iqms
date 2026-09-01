<?php

namespace App\Filament\Resources\ReadinessRuns;
use App\Support\Tenancy\TenantQuery;

use App\Filament\Resources\ReadinessRuns\Pages\CreateReadinessRun;
use App\Filament\Resources\ReadinessRuns\Pages\EditReadinessRun;
use App\Filament\Resources\ReadinessRuns\Pages\ListReadinessRuns;
use App\Filament\Resources\ReadinessRuns\RelationManagers\GapsRelationManager;
use App\Filament\Resources\ReadinessRuns\RelationManagers\ResultsRelationManager;
use App\Filament\Resources\ReadinessRuns\Schemas\ReadinessRunForm;
use App\Filament\Resources\ReadinessRuns\Tables\ReadinessRunsTable;
use App\Models\ReadinessRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReadinessRunResource extends Resource
{
    protected static ?string $model = ReadinessRun::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Akreditasi';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Readiness Runs';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user?->isSuperAdmin()) {
            return $query;
        }
        return $query->whereHas('accreditation', fn (Builder $accreditation): Builder => TenantQuery::forOptionalProgramStudi($accreditation, $user));
    }

    public static function form(Schema $schema): Schema
    {
        return ReadinessRunForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadinessRunsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [ResultsRelationManager::class, GapsRelationManager::class];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReadinessRuns::route('/'),
            'create' => CreateReadinessRun::route('/create'),
            'edit' => EditReadinessRun::route('/{record}/edit'),
        ];
    }
}
