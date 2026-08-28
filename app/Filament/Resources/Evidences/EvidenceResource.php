<?php

declare(strict_types=1);

namespace App\Filament\Resources\Evidences;

use App\Domain\Evidence\StoreEvidenceLink;
use App\Filament\Resources\Evidences\Pages\CreateEvidence;
use App\Filament\Resources\Evidences\Pages\EditEvidence;
use App\Filament\Resources\Evidences\Pages\ListEvidences;
use App\Filament\Resources\Evidences\RelationManagers\EvidenceLinkChecksRelationManager;
use App\Filament\Resources\Evidences\RelationManagers\EvidenceReviewsRelationManager;
use App\Models\Evidence;
use App\Models\EvidenceReview;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EvidenceResource extends Resource
{
    protected static ?string $model = Evidence::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Evidence Center';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Evidence Cloud';

    protected static ?string $modelLabel = 'Evidence Cloud';

    protected static ?string $pluralModelLabel = 'Evidence Cloud';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['perguruanTinggi', 'programStudi', 'versions.document']);
        $user = auth()->user();
        if ($user === null || $user->isSuperAdmin()) {
            return $user === null ? $query->whereRaw('1 = 0') : $query;
        }

        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('perguruan_tinggi_id', $user->perguruan_tinggi_id ?? 0)
                ->when($user->programStudis()->exists(), function (Builder $q) use ($user): void {
                    $q->whereIn('program_studi_id', $user->programStudis()->pluck('program_studi.id'));
                });
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Evidence Cloud')
                ->description('Simpan metadata dan tautan dokumen pada Google Drive atau cloud storage institusi. File fisik tidak diunggah ke aplikasi SQM.')
                ->icon('heroicon-o-link')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
            Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt')->searchable()->preload()->required(),
            Select::make('program_studi_id')->label('Program Studi')->relationship('programStudi', 'nama_prodi')->searchable()->preload(),
            TextInput::make('code')->label('Kode Evidence')->required()->maxLength(80)->alphaDash()->unique(ignoreRecord: true),
            TextInput::make('title')->label('Judul')->required()->maxLength(255),
            Textarea::make('description')->columnSpanFull(),
            DatePicker::make('valid_from'),
            DatePicker::make('valid_until')->afterOrEqual('valid_from'),
            Select::make('status')->label('Status Evidence')->options(['draft' => 'Draf', 'submitted' => 'Dikirim', 'verified' => 'Terverifikasi', 'archived' => 'Diarsipkan'])->default('draft')->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable(),
            TextColumn::make('title')->label('Judul')->searchable()->sortable()->wrap(),
            TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->sortable(),
            TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->sortable()->placeholder('Tingkat Perguruan Tinggi'),
            TextColumn::make('status')->label('Status Evidence')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state))->sortable(),
            TextColumn::make('versions_count')->counts('versions')->label('Versi')->sortable(),
            TextColumn::make('valid_until')->label('Berlaku Sampai')->date()->sortable(),
            TextColumn::make('versions.document.storage_provider')->label('Penyimpanan')->badge()->placeholder('—'),
            TextColumn::make('versions.document.external_url')->label('Tautan Cloud')->url(fn (?string $state): ?string => $state)->limit(35)->openUrlInNewTab()->placeholder('—'),
        ])->recordActions([
            Action::make('addVersion')->label('Tambah Link Versi')->icon(Heroicon::OutlinedLink)->form([
                TextInput::make('external_url')->label('Tautan Dokumen Cloud')->url()->rules(['url', 'regex:/^https:\/\//i'])->required()->maxLength(2000)->helperText('Simpan link Google Drive atau cloud storage institusi. File tidak diunggah ke aplikasi SQM.'),
                TextInput::make('original_name')->label('Nama Dokumen')->maxLength(255),
                TextInput::make('mime_type')->label('Tipe Dokumen')->maxLength(150)->placeholder('application/pdf'),
                TextInput::make('size_bytes')->label('Ukuran (byte)')->numeric()->minValue(0),
                TextInput::make('sha256')->label('SHA-256 (opsional)')->length(64)->alphaDash(),
                TextInput::make('external_folder_url')->label('Tautan Folder Cloud (opsional)')->url()->rules(['url', 'regex:/^https:\/\//i'])->maxLength(500),
                Select::make('link_access_mode')->label('Akses Link')->options([
                    'institution_managed' => 'Dikelola Institusi',
                    'restricted' => 'Restricted / Pengguna Tertentu',
                    'anyone_with_link' => 'Anyone with Link',
                ])->default('institution_managed')->required(),
                TextInput::make('change_reason')->label('Alasan Perubahan')->maxLength(255),
            ])->action(function (Evidence $record, array $data): void {
                app(StoreEvidenceLink::class)->handle(auth()->user(), $record, $data['external_url'], $data);
            }),
            Action::make('review')
                ->label('Review Evidence Cloud')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->form([
                    Select::make('status')->label('Hasil Review')->options(['accepted' => 'Diterima', 'rejected' => 'Ditolak', 'needs_revision' => 'Perlu Perbaikan'])->required(),
                    Textarea::make('notes')->label('Catatan Reviewer')->required(),
                ])
                ->action(function (Evidence $record, array $data): void {
                    $version = $record->versions()->latest('version_no')->first();
                    EvidenceReview::create([
                        'evidence_id' => $record->getKey(),
                        'evidence_version_id' => $version?->getKey(),
                        'reviewer_id' => auth()->id(),
                        'status' => $data['status'],
                        'notes' => $data['notes'],
                        'reviewed_at' => now(),
                    ]);
                    $record->update([
                        'status' => $data['status'] === 'accepted' ? 'verified' : ($data['status'] === 'rejected' ? 'draft' : 'submitted'),
                        'verified_by' => $data['status'] === 'accepted' ? auth()->id() : null,
                        'verified_at' => $data['status'] === 'accepted' ? now() : null,
                    ]);
                }),
        ])->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [EvidenceReviewsRelationManager::class, EvidenceLinkChecksRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => ListEvidences::route('/'), 'create' => CreateEvidence::route('/create'), 'edit' => EditEvidence::route('/{record}/edit')];
    }
}
