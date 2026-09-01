<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\RelationManagers;

use App\Models\Evidence;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvidenceLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'evidenceLinks';

    protected static ?string $title = 'Dokumen Bukti Tertaut (Evidence Cloud)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Tautkan Dokumen Bukti')
                ->description('Pilih dokumen bukti dari Evidence Cloud institusi yang menjadi dasar temuan audit.')
                ->icon('heroicon-o-paper-clip')
                ->columns(2)
                ->schema([
                    Select::make('evidence_id')
                        ->label('Pilih Dokumen Bukti')
                        ->options(function (): array {
                            $owner = $this->getOwnerRecord();
                            $owner->loadMissing('cycle');
                            $ptId = $owner->cycle?->perguruan_tinggi_id;

                            return Evidence::query()
                                ->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))
                                ->whereHas('versions.document', fn ($query) => $query->whereNotNull('external_url'))
                                ->orderBy('title')
                                ->pluck('title', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn (?object $record): bool => $record !== null)
                        ->helperText('Hanya dokumen bukti dengan tautan cloud aktif yang ditampilkan.'),
                    Select::make('relation_type')
                        ->label('Jenis Bukti')
                        ->options([
                            'audit_evidence' => 'Bukti Temuan Lapangan',
                            'supporting_evidence' => 'Bukti Pendukung',
                            'policy_document' => 'Dokumen Acuan Standar',
                        ])
                        ->default('audit_evidence')
                        ->required(),
                    TextInput::make('citation_page')
                        ->label('Halaman / Bab Rujukan')
                        ->placeholder('Contoh: Hlm. 12-15 atau Bab 3')
                        ->maxLength(100),
                    TextInput::make('citation_note')
                        ->label('Catatan Keterangan')
                        ->placeholder('Catatan rujukan konteks temuan...')
                        ->maxLength(255),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['evidence.versions.document']))
            ->columns([
                TextColumn::make('evidence.code')
                    ->label('Kode Bukti')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('evidence.title')
                    ->label('Judul Dokumen Bukti')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('relation_type')
                    ->label('Jenis Bukti')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'audit_evidence' => 'Bukti Temuan',
                        'supporting_evidence' => 'Pendukung',
                        'policy_document' => 'Acuan Standar',
                        default => $state ?? '—',
                    })
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'audit_evidence' => 'danger',
                        'policy_document' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('citation_page')
                    ->label('Halaman / Bab')
                    ->placeholder('—'),
                TextColumn::make('citation_note')
                    ->label('Catatan')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('evidence.versions.document.external_url')
                    ->label('Tautan Cloud')
                    ->url(fn (?string $state): ?string => $state)
                    ->limit(25)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                TextColumn::make('evidence.status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tautkan Bukti Baru')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['is_required'] = true;
                        return $data;
                    }),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Ubah Sitasi'),
                \Filament\Actions\DeleteAction::make()->label('Lepas Tautan'),
            ])
            ->toolbarActions([]);
    }
}
