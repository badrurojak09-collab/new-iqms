<?php

namespace App\Filament\Resources\AssessmentScales;

use App\Filament\Resources\AssessmentScales\Pages\CreateAssessmentScale;
use App\Filament\Resources\AssessmentScales\Pages\EditAssessmentScale;
use App\Filament\Resources\AssessmentScales\Pages\ListAssessmentScales;
use App\Filament\Resources\AssessmentScales\RelationManagers\OptionsRelationManager;
use App\Filament\Resources\AssessmentScales\Schemas\AssessmentScaleForm;
use App\Filament\Resources\AssessmentScales\Tables\AssessmentScalesTable;
use App\Models\AssessmentScale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssessmentScaleResource extends Resource
{
    protected static ?string $model = AssessmentScale::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 80;

    protected static ?string $navigationLabel = 'Skala Penilaian';

    protected static ?string $modelLabel = 'Skala Penilaian';

    protected static ?string $pluralModelLabel = 'Skala Penilaian';

    public static function form(Schema $schema): Schema
    {
        return AssessmentScaleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentScalesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [OptionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssessmentScales::route('/'),
            'create' => CreateAssessmentScale::route('/create'),
            'edit' => EditAssessmentScale::route('/{record}/edit'),
        ];
    }
}
