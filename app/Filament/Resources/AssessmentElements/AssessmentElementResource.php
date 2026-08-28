<?php

namespace App\Filament\Resources\AssessmentElements;

use App\Filament\Resources\AssessmentElements\Pages\CreateAssessmentElement;
use App\Filament\Resources\AssessmentElements\Pages\EditAssessmentElement;
use App\Filament\Resources\AssessmentElements\Pages\ListAssessmentElements;
use App\Filament\Resources\AssessmentElements\Schemas\AssessmentElementForm;
use App\Filament\Resources\AssessmentElements\Tables\AssessmentElementsTable;
use App\Models\AssessmentElement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AssessmentElementResource extends Resource
{
    protected static ?string $model = AssessmentElement::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 60;

    protected static ?string $navigationLabel = 'Elemen Penilaian';

    protected static ?string $modelLabel = 'Elemen Penilaian';

    protected static ?string $pluralModelLabel = 'Elemen Penilaian';

    public static function form(Schema $schema): Schema
    {
        return AssessmentElementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssessmentElementsTable::configure($table);
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
            'index' => ListAssessmentElements::route('/'),
            'create' => CreateAssessmentElement::route('/create'),
            'edit' => EditAssessmentElement::route('/{record}/edit'),
        ];
    }
}
