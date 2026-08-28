<?php

declare(strict_types=1);

namespace App\Filament\Resources\InstrumentVersions;

use App\Domain\InstrumentRegistry\ImportCanonicalInstrument;
use App\Filament\Resources\InstrumentVersions\Pages\CreateInstrumentVersion;
use App\Filament\Resources\InstrumentVersions\Pages\EditInstrumentVersion;
use App\Filament\Resources\InstrumentVersions\Pages\ListInstrumentVersions;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class InstrumentVersionResource extends Resource
{
    protected static ?string $model = InstrumentVersion::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Instrument Registry';

    protected static ?int $navigationSort = 30;

    protected static ?string $navigationLabel = 'Versi Instrumen';

    protected static ?string $modelLabel = 'Versi Instrumen';

    protected static ?string $pluralModelLabel = 'Versi Instrumen';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Versi Instrumen')
                ->description('Kelola versi instrumen dengan histori perubahan. Versi yang sudah diterbitkan tidak dapat diubah.')
                ->icon('heroicon-o-document-check')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_family_id')->label('Keluarga Instrumen')->relationship('family', 'name')->searchable()->preload()->required(),
                    Select::make('parent_version_id')->label('Versi Induk')->relationship('parent', 'version_label')->searchable()->preload()->helperText('Gunakan jika versi ini merupakan pembaruan dari versi sebelumnya.'),
                    TextInput::make('version_label')->label('Label Versi')->required()->maxLength(50),
                    Select::make('status')->label('Status Versi')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'published' => 'Diterbitkan', 'retired' => 'Tidak Berlaku'])->required()->default('draft'),
                    TextInput::make('source_reference')->label('Referensi Sumber')->url()->maxLength(255),
                    DatePicker::make('effective_from')->label('Mulai Berlaku')->native(false),
                    DatePicker::make('effective_until')->label('Berakhir Berlaku')->native(false)->afterOrEqual('effective_from'),
                    Textarea::make('changelog')->label('Catatan Perubahan')->helperText('Tuliskan perubahan dibandingkan versi induk. Format JSON tetap didukung oleh engine.')->rows(5)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('family.name')->label('Keluarga Instrumen')->searchable()->sortable(),
            TextColumn::make('version_label')->label('Label Versi')->searchable()->sortable(),
            TextColumn::make('status')->label('Status Versi')->badge()->formatStateUsing(fn (?string $state): string => match ($state) {
                'review' => 'Dalam Review',
                'published' => 'Diterbitkan',
                'retired' => 'Tidak Berlaku',
                default => 'Draf',
            })->sortable(),
            TextColumn::make('effective_from')->label('Mulai Berlaku')->date()->sortable(),
            TextColumn::make('effective_until')->label('Berakhir Berlaku')->date()->placeholder('—')->sortable(),
            TextColumn::make('content_hash')->label('Hash Konten')->limit(18)->copyable()->placeholder('Belum Dihash'),
            TextColumn::make('nodes_count')->counts('nodes')->label('Jumlah Elemen')->sortable(),
        ])->headerActions([
            Action::make('importCanonical')
                ->label('Import Konfigurasi')
                ->icon(Heroicon::OutlinedArrowUpTray)
                ->form([
                    Select::make('instrument_family_id')->label('Keluarga Instrumen')->relationship('family', 'name')->searchable()->preload()->required(),
                    TextInput::make('version_label')->label('Label Versi')->required()->maxLength(50),
                    FileUpload::make('file')->label('File Konfigurasi')->disk('local')->directory('tmp/instrument-import')->storeFiles(false)->required()->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->helperText('Format yang didukung: CSV atau Excel. File akan diproses sebagai konfigurasi instrumen canonical.'),
                ])
                ->action(function (array $data): void {
                    $path = is_string($data['file']) ? Storage::disk('local')->path($data['file']) : $data['file']->getRealPath();
                    app(ImportCanonicalInstrument::class)->commit(auth()->user(), InstrumentFamily::query()->findOrFail($data['instrument_family_id']), $path, basename($path), $data['version_label']);
                }),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListInstrumentVersions::route('/'), 'create' => CreateInstrumentVersion::route('/create'), 'edit' => EditInstrumentVersion::route('/{record}/edit')];
    }

    public static function canEdit($record): bool
    {
        return parent::canEdit($record) && ! $record->isImmutable();
    }

    public static function canDelete($record): bool
    {
        return parent::canDelete($record) && ! $record->isImmutable();
    }
}
