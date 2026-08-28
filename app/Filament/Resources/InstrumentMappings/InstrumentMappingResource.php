<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstrumentMappings;

use App\Models\AccreditationCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentIndicator;
use App\Models\InstrumentMapping;
use App\Models\InstrumentNode;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstrumentMappingResource extends Resource
{
    protected static ?string $model = InstrumentMapping::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 120;

    protected static ?string $navigationLabel = 'Pemetaan Instrumen';

    protected static ?string $modelLabel = 'Pemetaan Instrumen';

    protected static ?string $pluralModelLabel = 'Pemetaan Instrumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pemetaan Instrumen')
                ->description('Hubungkan indikator AMI/SPMI dan sumber mutu internal dengan elemen instrumen akreditasi.')
                ->icon('heroicon-o-arrows-right-left')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
            Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
            Select::make('instrument_node_id')->label('Elemen Instrumen Target')->options(fn (Get $get): array => InstrumentNode::query()->where('instrument_version_id', $get('instrument_version_id'))->orderBy('sort_order')->pluck('title', 'id')->all())->searchable()->preload()->required(),
            Select::make('source_indicator_id')->label('Indikator Sumber AMI/SPMI')->options(fn (Get $get): array => AssessmentIndicator::query()->whereHas('element.criterion', fn ($q) => $q->where('instrument_version_id', $get('instrument_version_id')))->orderBy('code')->pluck('name', 'id')->all())->searchable()->preload(),
            Select::make('target_element_id')->label('Elemen Kanonik Target')->options(fn (Get $get): array => AssessmentElement::query()->whereHas('criterion', fn ($q) => $q->where('instrument_version_id', $get('instrument_version_id')))->orderBy('code')->pluck('title', 'id')->all())->searchable()->preload(),
            Select::make('accreditation_criterion_id')->label('Kriteria Akreditasi')->options(fn (Get $get): array => AccreditationCriterion::query()->where('instrument_version_id', $get('instrument_version_id'))->orderBy('sort_order')->pluck('name', 'id')->all())->searchable()->preload(),
            Select::make('source_type')->label('Jenis Sumber')->options(['ami_indicator' => 'Indikator AMI', 'spmi_indicator' => 'Indikator SPMI', 'instrument_node' => 'Elemen Instrumen', 'ami_finding' => 'Temuan AMI'])->required()->default('ami_indicator'),
            Select::make('mapping_type')->label('Jenis Pemetaan')->options(['supports' => 'Mendukung', 'measures' => 'Mengukur', 'satisfies' => 'Memenuhi', 'derived_from' => 'Berasal Dari', 'requires' => 'Membutuhkan'])->required()->default('supports'),
            Select::make('target_type')->label('Jenis Target')->options(['banpt_element' => 'Elemen BAN-PT', 'lam_element' => 'Elemen LAM', 'led' => 'LED', 'lkps' => 'LKPS', 'response' => 'Respons'])->required()->default('banpt_element'),
            TextInput::make('target_key')->label('Kunci Target')->maxLength(120),
            TextInput::make('coverage_weight')->label('Bobot Cakupan')->numeric()->minValue(0)->maxValue(100)->suffix('%'),
            Select::make('approval_status')->label('Status Approval')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku'])->required()->default('draft'),
            Select::make('is_required')->label('Pemetaan Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib'])->default(0)->required(),
            TextInput::make('source_reference')->label('Referensi Sumber')->maxLength(500),
            Textarea::make('notes')->label('Catatan')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable()->searchable(),
            TextColumn::make('instrumentNode.code')->label('Kode Elemen Instrumen')->sortable(),
            TextColumn::make('criterion.code')->label('Kode Kriteria')->sortable(),
            TextColumn::make('sourceIndicator.code')->label('Kode Sumber AMI/SPMI')->placeholder('—')->sortable(),
            TextColumn::make('targetElement.code')->label('Kode Elemen Target')->placeholder('—')->sortable(),
            TextColumn::make('mapping_type')->label('Jenis Pemetaan')->formatStateUsing(fn (?string $state): string => match ($state) {
                'supports' => 'Mendukung', 'measures' => 'Mengukur', 'satisfies' => 'Memenuhi', 'derived_from' => 'Berasal Dari', 'requires' => 'Membutuhkan', default => $state ?: '—',
            })->badge(),
            TextColumn::make('approval_status')->label('Status Approval')->formatStateUsing(fn (?string $state): string => match ($state) {
                'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku', default => 'Draf',
            })->badge()->sortable(),
            TextColumn::make('target_type')->label('Jenis Target')->badge(),
            TextColumn::make('target_key')->label('Kunci Target')->searchable(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInstrumentMappings::route('/'), 'create' => Pages\CreateInstrumentMapping::route('/create'), 'edit' => Pages\EditInstrumentMapping::route('/{record}/edit')];
    }
}
