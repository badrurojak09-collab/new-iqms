<?php

declare(strict_types=1);

namespace App\Filament\Resources\EvidenceCollections\RelationManagers;

use App\Domain\Evidence\EvidenceCollectionService;
use App\Models\Evidence;
use App\Models\EvidenceCollectionItem;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Item Kebutuhan Evidence';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kebutuhan Evidence')
                ->description('Tentukan persyaratan evidence, target yang didukung, dan tautan cloud yang digunakan.')
                ->icon('heroicon-o-link')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('requirement_code')->label('Kode Persyaratan')->required()->maxLength(150)->alphaDash(),
                    TextInput::make('requirement_title')->label('Judul Persyaratan')->required()->maxLength(500),
                    Select::make('evidence_id')->label('Evidence Cloud')->options(fn (): array => $this->evidenceOptions())->searchable()->preload()->helperText('Pilih evidence yang sudah tersimpan di Evidence Center.'),
                    Select::make('target_type')->label('Jenis Target')->options(['assessment_response' => 'Respons Assessment', 'instrument_node' => 'Elemen Instrumen', 'ami_finding' => 'Temuan AMI', 'led_section' => 'Bagian LED', 'lkps_column' => 'Kolom LKPS'])->helperText('Target yang menggunakan persyaratan evidence ini.'),
                    TextInput::make('target_id')->label('ID Target')->numeric()->helperText('ID teknis target sesuai jenis target yang dipilih.'),
                    Select::make('is_required')->label('Persyaratan Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib'])->default(1)->required(),
                    Textarea::make('notes')->label('Catatan')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('requirement_code')->label('Kode Persyaratan')->searchable()->sortable(),
                TextColumn::make('requirement_title')->label('Judul Persyaratan')->wrap()->searchable(),
                TextColumn::make('evidence.code')->label('Kode Evidence')->placeholder('Belum Ditautkan')->sortable(),
                TextColumn::make('evidence.title')->label('Evidence Cloud')->placeholder('Belum Ditautkan')->wrap()->searchable(),
                TextColumn::make('evidence.status')->label('Status Evidence')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'submitted' => 'Dikirim',
                    'verified' => 'Terverifikasi',
                    'archived' => 'Diarsipkan',
                    default => 'Draf',
                })->badge()->placeholder('—'),
                TextColumn::make('status')->label('Status Item')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'linked' => 'Tertaut',
                    'verified' => 'Terverifikasi',
                    'rejected' => 'Ditolak',
                    default => 'Belum Terpenuhi',
                })->badge()->sortable(),
                TextColumn::make('is_required')->label('Wajib')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Kebutuhan Evidence')->visible(fn (): bool => $this->getOwnerRecord()->status !== 'locked'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit')->visible(fn (): bool => $this->getOwnerRecord()->status !== 'locked'),
                Action::make('attachExistingEvidence')
                    ->label('Lampirkan Evidence yang Ada')
                    ->icon(Heroicon::OutlinedPaperClip)
                    ->visible(fn (): bool => $this->getOwnerRecord()->status !== 'locked')
                    ->form([
                        Select::make('evidence_id')->label('Evidence Cloud')->options(fn (): array => $this->evidenceOptions())->searchable()->preload()->required()->helperText('Hanya evidence dengan versi cloud yang tersedia yang ditampilkan.'),
                    ])
                    ->action(function (EvidenceCollectionItem $record, array $data): void {
                        $evidence = Evidence::query()->findOrFail($data['evidence_id']);
                        app(EvidenceCollectionService::class)->attachEvidence(auth()->user(), $record, $evidence);
                    }),
                Action::make('checkLink')->label('Periksa Tautan')->icon(Heroicon::OutlinedLink)->visible(fn (EvidenceCollectionItem $record): bool => $record->evidence_id !== null)->action(function (EvidenceCollectionItem $record): void {
                    if ($record->evidence) {
                        app(EvidenceCollectionService::class)->checkLatestLink(auth()->user(), $record->evidence);
                    }
                }),
                DeleteAction::make()->label('Hapus')->visible(fn (): bool => $this->getOwnerRecord()->status !== 'locked'),
            ])
            ->toolbarActions([]);
    }

    /** @return array<int|string, string> */
    private function evidenceOptions(): array
    {
        return Evidence::query()
            ->where('perguruan_tinggi_id', $this->getOwnerRecord()->perguruan_tinggi_id)
            ->whereHas('versions.document', fn ($query) => $query->whereNotNull('external_url'))
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (Evidence $evidence): array => [$evidence->getKey() => sprintf('%s — %s (%s)', $evidence->code, $evidence->title, match ($evidence->status) {
                'verified' => 'Terverifikasi',
                'submitted' => 'Dikirim',
                default => 'Draf',
            })])
            ->all();
    }
}
