<?php declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;

class AmiChecklistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['cycle.perguruanTinggi', 'cycle.programStudi']))
            ->columns([
                Stack::make([
                    Split::make([
                        // Kolom Kiri: Kode & Info Institusi
                        Stack::make([
                            TextColumn::make('code')
                                ->label('Kode')
                                ->weight('bold')
                                ->searchable()
                                ->sortable(),
                            TextColumn::make('cycle.code')
                                ->label('Siklus AMI')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('cycle.perguruanTinggi.nama_pt')
                                ->label('Perguruan Tinggi')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('cycle.programStudi.nama_prodi')
                                ->label('Program Studi')
                                ->placeholder('Tingkat Perguruan Tinggi')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                        ])->space(1),
                        // Kolom Kanan: Status & Badge
                        Stack::make([
                            TextColumn::make('response_status')
                                ->label('Status Respons')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'completed' => 'Selesai',
                                    'verified' => 'Terverifikasi',
                                    default => 'Belum Dimulai',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'completed', 'verified' => 'success',
                                    'in_progress' => 'warning',
                                    default => 'gray',
                                }),
                            TextColumn::make('response_type')
                                ->label('Jenis Respons')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'boolean' => 'Ya/Tidak',
                                    'numeric' => 'Numerik',
                                    'choice' => 'Pilihan',
                                    default => 'Teks',
                                })
                                ->badge()
                                ->color('info'),
                            TextColumn::make('evidence_required')
                                 ->label('Evidence')
                                 ->formatStateUsing(fn($state): string => $state ? 'Wajib Evidence' : 'Tidak Wajib Evidence')
                                 ->badge()
                                 ->color(fn($state): string => $state ? 'danger' : 'gray'),
                            TextColumn::make('evidenceLinks.evidence.title')
                                ->label('Bukti Audit')
                                ->badge()
                                ->color('info')
                                ->placeholder('0 Bukti')
                                ->limitList(1)
                                ->expandableLimitedList(),
                        ])->space(1),
                    ]),
                    // Teks Pertanyaan Audit Utuh
                    TextColumn::make('question')
                        ->label('Pertanyaan Audit')
                        ->wrap()
                        ->extraAttributes(['class' => 'pt-2 font-medium text-gray-900 dark:text-gray-100'])
                        ->searchable(),
                ])->space(3),
            ])
            // contentGrid dihapus agar tidak menjadi double card
            ->filters([
                SelectFilter::make('response_status')->label('Status Respons')->options(['not_started' => 'Belum Dimulai', 'in_progress' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi']),
                SelectFilter::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'boolean' => 'Ya/Tidak', 'numeric' => 'Numerik', 'choice' => 'Pilihan']),
                SelectFilter::make('evidence_required')->label('Evidence')->options([1 => 'Wajib', 0 => 'Tidak Wajib']),
            ])
            ->filters([TrashedFilter::make()->label('Data Terhapus')])
            ->recordActions([
                RestoreAction::make()->label('Pulihkan')->visible(fn ($record): bool => $record->trashed()),
                ForceDeleteAction::make()->label('Hapus Permanen')->visible(fn ($record): bool => $record->trashed()),
                EditAction::make()->label('Edit'),
                \Filament\Actions\Action::make('linkEvidence')
                    ->label('Tautkan Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->visible(fn ($record): bool => auth()->user()?->can('manage ami') || auth()->user()?->can('review ami'))
                    ->form([
                        \Filament\Forms\Components\Select::make('evidence_id')
                            ->label('Pilih Dokumen Bukti (Evidence Cloud)')
                            ->options(function ($record): array {
                                $ptId = $record->cycle?->perguruan_tinggi_id;

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
                        \Filament\Forms\Components\Select::make('relation_type')
                            ->label('Jenis Bukti')
                            ->options([
                                'audit_evidence' => 'Bukti Verifikasi Checklist',
                                'supporting_evidence' => 'Bukti Pendukung',
                                'policy_document' => 'Dokumen Acuan Standar',
                            ])
                            ->default('audit_evidence')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('citation_page')
                            ->label('Halaman / Bab Rujukan')
                            ->placeholder('Contoh: Hlm. 5-8')
                            ->maxLength(100),
                        \Filament\Forms\Components\TextInput::make('citation_note')
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
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
