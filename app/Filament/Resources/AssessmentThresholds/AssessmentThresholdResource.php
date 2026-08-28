<?php

namespace App\Filament\Resources\AssessmentThresholds;

use App\Filament\Resources\AssessmentThresholds\Pages\CreateAssessmentThreshold;
use App\Filament\Resources\AssessmentThresholds\Pages\EditAssessmentThreshold;
use App\Filament\Resources\AssessmentThresholds\Pages\ListAssessmentThresholds;
use App\Filament\Resources\AssessmentThresholds\Schemas\AssessmentThresholdForm;
use App\Filament\Resources\AssessmentThresholds\Tables\AssessmentThresholdsTable;
use App\Models\AssessmentThreshold;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssessmentThresholdResource extends Resource
{
    protected static ?string $model = AssessmentThreshold::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'Ambang Batas';

    protected static ?string $modelLabel = 'Ambang Batas';

    protected static ?string $pluralModelLabel = 'Ambang Batas';

    public static function form(Schema $schema): Schema
    {
        return AssessmentThresholdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentThresholdsTable::configure($table);
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
            'index' => ListAssessmentThresholds::route('/'),
            'create' => CreateAssessmentThreshold::route('/create'),
            'edit' => EditAssessmentThreshold::route('/{record}/edit'),
        ];
    }
}
