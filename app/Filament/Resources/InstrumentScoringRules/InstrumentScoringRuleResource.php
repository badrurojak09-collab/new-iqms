<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstrumentScoringRules;

use App\Models\InstrumentScoringRule;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstrumentScoringRuleResource extends Resource
{
    protected static ?string $model = InstrumentScoringRule::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 110;

    protected static ?string $navigationLabel = 'Aturan Scoring';

    protected static ?string $modelLabel = 'Aturan Scoring';

    protected static ?string $pluralModelLabel = 'Aturan Scoring';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Aturan Scoring Instrumen')
                ->description('Konfigurasikan cara engine menghitung skor, mengevaluasi threshold, dan menentukan status kualifikasi.')
                ->icon('heroicon-o-calculator')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Aturan')->required()->maxLength(100)->alphaDash()->unique(ignoreRecord: true),
                    Select::make('rule_type')->label('Jenis Aturan')->options(['weighted_sum' => 'Jumlah Berbobot', 'threshold' => 'Ambang Batas', 'formula' => 'Formula', 'mapping' => 'Pemetaan', 'status_qualification' => 'Kualifikasi Status'])->required(),
                    KeyValue::make('expression')->label('Ekspresi Aturan')->keyLabel('Kunci')->valueLabel('Nilai')->required()->columnSpanFull(),
                    KeyValue::make('parameters')->label('Parameter Aturan')->keyLabel('Parameter')->valueLabel('Nilai')->columnSpanFull()->helperText('Parameter JSON yang digunakan runtime evaluator.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('instrumentVersion.family.name')->label('Keluarga Instrumen')->sortable()->searchable(),
            TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
            TextColumn::make('code')->label('Kode Aturan')->searchable()->sortable(),
            TextColumn::make('rule_type')->label('Jenis Aturan')->formatStateUsing(fn (?string $state): string => match ($state) {
                'weighted_sum' => 'Jumlah Berbobot', 'threshold' => 'Ambang Batas', 'formula' => 'Formula', 'mapping' => 'Pemetaan', 'status_qualification' => 'Kualifikasi Status', default => $state ?: '—',
            })->badge(),
            TextColumn::make('updated_at')->label('Diperbarui Pada')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInstrumentScoringRules::route('/'),
            'create' => Pages\CreateInstrumentScoringRule::route('/create'),
            'edit' => Pages\EditInstrumentScoringRule::route('/{record}/edit'),
        ];
    }
}
