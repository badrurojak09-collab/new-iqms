<?php

namespace App\Filament\Resources\AssessmentRubrics;

use App\Filament\Resources\AssessmentRubrics\Pages\CreateAssessmentRubric;
use App\Filament\Resources\AssessmentRubrics\Pages\EditAssessmentRubric;
use App\Filament\Resources\AssessmentRubrics\Pages\ListAssessmentRubrics;
use App\Filament\Resources\AssessmentRubrics\Schemas\AssessmentRubricForm;
use App\Filament\Resources\AssessmentRubrics\Tables\AssessmentRubricsTable;
use App\Models\AssessmentRubric;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssessmentRubricResource extends Resource
{
    protected static ?string $model = AssessmentRubric::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 90;

    protected static ?string $navigationLabel = 'Rubrik Penilaian';

    protected static ?string $modelLabel = 'Rubrik Penilaian';

    protected static ?string $pluralModelLabel = 'Rubrik Penilaian';

    public static function form(Schema $schema): Schema
    {
        return AssessmentRubricForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentRubricsTable::configure($table);
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
            'index' => ListAssessmentRubrics::route('/'),
            'create' => CreateAssessmentRubric::route('/create'),
            'edit' => EditAssessmentRubric::route('/{record}/edit'),
        ];
    }
}
