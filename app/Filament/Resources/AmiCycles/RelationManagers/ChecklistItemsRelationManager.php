<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\RelationManagers;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Tenancy\TenantQuery;
use App\Models\InstrumentNode;
use App\Models\SpmiIndicator;
use App\Models\SpmiStandard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistItems';

    protected static ?string $title = 'Checklist Audit';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('question')->label('Pertanyaan')->wrap()->searchable(),
                TextColumn::make('response_type')->label('Jenis Respons')->badge(),
                TextColumn::make('response_status')->label('Status Respons')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'in_progress' => 'Sedang Dikerjakan',
                    'completed' => 'Selesai',
                    'verified' => 'Terverifikasi',
                    default => 'Belum Dimulai',
                })->badge(),
                TextColumn::make('score')->label('Skor')->placeholder('—'),
                TextColumn::make('evidenceLinks.evidence.title')
                    ->label('Bukti Audit')
                    ->badge()
                    ->color('info')
                    ->placeholder('0 Bukti')
                    ->limitList(1)
                    ->expandableLimitedList(),
                TextColumn::make('evidence_required')->label('Evidence')->formatStateUsing(fn ($state): string => $state ? 'Wajib' : 'Tidak Wajib')->badge(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Checklist')
                    ->visible(fn (): bool => auth()->user()?->can('manage ami') ?? false)
                    ->form(self::schemaFields()),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('linkEvidence')
                    ->label('Tautkan Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->visible(fn (): bool => auth()->user()?->can('manage ami') || auth()->user()?->can('review ami'))
                    ->form([
                        Select::make('evidence_id')
                            ->label('Pilih Dokumen Bukti (Evidence Cloud)')
                            ->options(function ($record): array {
                                $ptId = $record->cycle?->perguruan_tinggi_id ?? $this->getOwnerRecord()->perguruan_tinggi_id;

                                return \App\Models\Evidence::query()
                                    ->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))
                                    ->whereHas('versions.document', fn ($query) => $query->whereNotNull('external_url'))
                                    ->orderBy('title')
                                    ->pluck('title', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Hanya evidence dengan versi tautan cloud yang tersedia yang ditampilkan.'),
                        Select::make('relation_type')
                            ->label('Jenis Bukti')
                            ->options([
                                'audit_evidence' => 'Bukti Verifikasi Checklist',
                                'supporting_evidence' => 'Bukti Pendukung',
                                'policy_document' => 'Dokumen Acuan Standar',
                            ])
                            ->default('audit_evidence')
                            ->required(),
                        TextInput::make('citation_page')
                            ->label('Halaman / Bab Rujukan')
                            ->placeholder('Contoh: Hlm. 5-8')
                            ->maxLength(100),
                        TextInput::make('citation_note')
                            ->label('Catatan Keterangan')
                            ->placeholder('Catatan verifikasi dokumen...')
                            ->maxLength(255),
                    ])
                    ->action(function (\App\Models\AmiChecklistItem $record, array $data): void {
                        \App\Models\EvidenceLink::query()->updateOrCreate([
                            'evidence_id' => $data['evidence_id'],
                            'linkable_type' => \App\Models\AmiChecklistItem::class,
                            'linkable_id' => $record->getKey(),
                        ], [
                            'relation_type' => $data['relation_type'],
                            'citation_page' => $data['citation_page'] ?? null,
                            'citation_note' => $data['citation_note'] ?? null,
                            'is_required' => true,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Bukti berhasil ditautkan ke checklist audit.')
                            ->success()
                            ->send();
                    }),
                \Filament\Actions\EditAction::make()->label('Edit')->visible(fn (): bool => auth()->user()?->can('manage ami') ?? false),
                \Filament\Actions\DeleteAction::make()->label('Hapus')->visible(fn (): bool => auth()->user()?->can('manage ami') ?? false),
            ])
            ->toolbarActions([]);
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    private static function schemaFields(): array
    {
        return [
            TextInput::make('code')->label('Kode Checklist')->required()->maxLength(100),
            Textarea::make('question')->label('Pertanyaan Audit')->required()->rows(4)->columnSpanFull(),
            Select::make('instrument_node_id')->label('Elemen Instrumen')->options(fn (): array => InstrumentNode::query()->orderBy('code')->get()->mapWithKeys(fn (InstrumentNode $node): array => [$node->id => $node->code.' — '.$node->title])->all())->searchable()->preload(),
            Select::make('spmi_standard_id')->label('Standar SPMI')->options(fn (): array => SpmiStandard::query()->orderBy('code')->get()->mapWithKeys(fn (SpmiStandard $standard): array => [$standard->id => $standard->code.' — '.$standard->name])->all())->searchable()->preload(),
            Select::make('spmi_indicator_id')->label('Indikator SPMI')->options(fn (): array => SpmiIndicator::query()->orderBy('code')->get()->mapWithKeys(fn (SpmiIndicator $indicator): array => [$indicator->id => $indicator->code.' — '.$indicator->name])->all())->searchable()->preload(),
            Select::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'boolean' => 'Ya/Tidak', 'numeric' => 'Numerik', 'choice' => 'Pilihan'])->default('text')->required(),
            Select::make('response_status')->label('Status Respons')->options(['not_started' => 'Belum Dimulai', 'in_progress' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi'])->default('not_started')->required(),
            TextInput::make('score')->label('Skor')->numeric()->minValue(0),
            Toggle::make('evidence_required')->label('Evidence Wajib')->default(false),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
            Textarea::make('response')->label('Respons Auditor')->rows(4)->columnSpanFull(),
            Textarea::make('auditor_notes')->label('Catatan Auditor')->rows(4)->columnSpanFull(),
        ];
    }
}
