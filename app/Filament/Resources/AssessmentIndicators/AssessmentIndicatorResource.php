<?php

namespace App\Filament\Resources\AssessmentIndicators;

use App\Filament\Resources\AssessmentIndicators\Pages\CreateAssessmentIndicator;
use App\Filament\Resources\AssessmentIndicators\Pages\EditAssessmentIndicator;
use App\Filament\Resources\AssessmentIndicators\Pages\ListAssessmentIndicators;
use App\Filament\Resources\AssessmentIndicators\Schemas\AssessmentIndicatorForm;
use App\Filament\Resources\AssessmentIndicators\Tables\AssessmentIndicatorsTable;
use App\Models\AssessmentIndicator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssessmentIndicatorResource extends Resource
{
    protected static ?string $model = AssessmentIndicator::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 70;

    protected static ?string $navigationLabel = 'Indikator Penilaian';

    public static function form(Schema $schema): Schema
    {
        return AssessmentIndicatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentIndicatorsTable::configure($table);
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
            'index' => ListAssessmentIndicators::route('/'),
            'create' => CreateAssessmentIndicator::route('/create'),
            'edit' => EditAssessmentIndicator::route('/{record}/edit'),
        ];
    }
}
