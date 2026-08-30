<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations;

use App\Filament\Resources\SpmiEvaluations\RelationManagers\ImprovementProgramsRelationManager;
use App\Filament\Resources\SpmiEvaluations\Pages\CreateSpmiEvaluation;
use App\Filament\Resources\SpmiEvaluations\Pages\EditSpmiEvaluation;
use App\Filament\Resources\SpmiEvaluations\Pages\ListSpmiEvaluations;
use App\Filament\Resources\SpmiEvaluations\Schemas\SpmiEvaluationForm;
use App\Filament\Resources\SpmiEvaluations\Tables\SpmiEvaluationsTable;
use App\Models\SpmiEvaluation;
use App\Support\Tenancy\TenantQuery;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SpmiEvaluationResource extends Resource
{
    protected static ?string $model = SpmiEvaluation::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'SPMI';
    protected static ?int $navigationSort = 60;
    protected static ?string $navigationLabel = 'Evaluasi SPMI';
    protected static ?string $modelLabel = 'Evaluasi SPMI';
    protected static ?string $pluralModelLabel = 'Evaluasi SPMI';

    public static function form(Schema $schema): Schema
    {
        return SpmiEvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiEvaluationsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forOptionalProgramStudi(parent::getEloquentQuery(), auth()->user());
    }

    public static function getRelations(): array
    {
        return [ImprovementProgramsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpmiEvaluations::route('/'),
            'create' => CreateSpmiEvaluation::route('/create'),
            'edit' => EditSpmiEvaluation::route('/{record}/edit'),
        ];
    }
}
