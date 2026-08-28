<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstrumentFamilies;

use App\Filament\Resources\InstrumentFamilies\Pages\CreateInstrumentFamily;
use App\Filament\Resources\InstrumentFamilies\Pages\EditInstrumentFamily;
use App\Filament\Resources\InstrumentFamilies\Pages\ListInstrumentFamilies;
use App\Models\InstrumentFamily;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class InstrumentFamilyResource extends Resource
{
    protected static ?string $model = InstrumentFamily::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Keluarga Instrumen';

    protected static ?string $modelLabel = 'Keluarga Instrumen';

    protected static ?string $pluralModelLabel = 'Keluarga Instrumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Keluarga Instrumen')
                ->description('Definisikan lembaga penerbit, jenis instrumen, dan lingkup penggunaannya.')
                ->icon('heroicon-o-rectangle-group')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('accreditation_body_id')->label('Lembaga Penilai')->relationship('accreditationBody', 'name')->searchable()->preload(),
                    TextInput::make('code')->label('Kode Keluarga')->required()->maxLength(80)->alphaDash()->unique(ignoreRecord: true),
                    TextInput::make('name')->label('Nama Keluarga Instrumen')->required()->maxLength(255),
                    Select::make('scope_type')->label('Lingkup')->options(['institution' => 'Institusi', 'program_study' => 'Program Studi', 'spmi' => 'SPMI', 'ami' => 'AMI'])->required(),
                    Textarea::make('description')->label('Deskripsi')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable()->copyable(),
            TextColumn::make('name')->label('Nama Keluarga')->searchable()->sortable()->wrap(),
            TextColumn::make('accreditationBody.name')->label('Lembaga Penilai')->sortable()->searchable(),
            TextColumn::make('scope_type')->label('Lingkup')->formatStateUsing(fn (?string $state): string => match ($state) {
                'institution' => 'Institusi',
                'program_study' => 'Program Studi',
                'spmi' => 'SPMI',
                'ami' => 'AMI',
                default => $state ?: '—',
            })->badge()->sortable(),
            TextColumn::make('versions_count')->counts('versions')->label('Jumlah Versi')->sortable(),
        ])->defaultSort('name');
    }

    public static function getPages(): array
    {
        return ['index' => ListInstrumentFamilies::route('/'), 'create' => CreateInstrumentFamily::route('/create'), 'edit' => EditInstrumentFamily::route('/{record}/edit')];
    }
}
