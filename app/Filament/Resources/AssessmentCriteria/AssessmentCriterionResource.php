<?php

namespace App\Filament\Resources\AssessmentCriteria;

use App\Filament\Resources\AssessmentCriteria\Pages\CreateAssessmentCriterion;
use App\Filament\Resources\AssessmentCriteria\Pages\EditAssessmentCriterion;
use App\Filament\Resources\AssessmentCriteria\Pages\ListAssessmentCriteria;
use App\Filament\Resources\AssessmentCriteria\Schemas\AssessmentCriterionForm;
use App\Filament\Resources\AssessmentCriteria\Tables\AssessmentCriteriaTable;
use App\Models\AssessmentCriterion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssessmentCriterionResource extends Resource
{
    protected static ?string $model = AssessmentCriterion::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'Kriteria Kanonik';

    public static function form(Schema $schema): Schema
    {
        return AssessmentCriterionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentCriteriaTable::configure($table);
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
            'index' => ListAssessmentCriteria::route('/'),
            'create' => CreateAssessmentCriterion::route('/create'),
            'edit' => EditAssessmentCriterion::route('/{record}/edit'),
        ];
    }
}
