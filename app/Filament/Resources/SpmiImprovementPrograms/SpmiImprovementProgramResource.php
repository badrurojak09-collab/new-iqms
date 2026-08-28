<?php

namespace App\Filament\Resources\SpmiImprovementPrograms;

use App\Filament\Resources\SpmiImprovementPrograms\Pages\CreateSpmiImprovementProgram;
use App\Filament\Resources\SpmiImprovementPrograms\Pages\EditSpmiImprovementProgram;
use App\Filament\Resources\SpmiImprovementPrograms\Pages\ListSpmiImprovementPrograms;
use App\Filament\Resources\SpmiImprovementPrograms\Schemas\SpmiImprovementProgramForm;
use App\Filament\Resources\SpmiImprovementPrograms\Tables\SpmiImprovementProgramsTable;
use App\Models\SpmiImprovementProgram;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SpmiImprovementProgramResource extends Resource
{
    protected static ?string $model = SpmiImprovementProgram::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'SPMI';

    protected static ?string $navigationLabel = 'Program Peningkatan SPMI';

    protected static ?string $modelLabel = 'Program Peningkatan SPMI';

    protected static ?string $pluralModelLabel = 'Program Peningkatan SPMI';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return SpmiImprovementProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpmiImprovementProgramsTable::configure($table);
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
            'index' => ListSpmiImprovementPrograms::route('/'),
            'create' => CreateSpmiImprovementProgram::route('/create'),
            'edit' => EditSpmiImprovementProgram::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
