<?php declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Tables;

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

class AmiFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['cycle.perguruanTinggi', 'evidenceLinks.evidence']))
            ->columns([
                Stack::make([
                    Split::make([
                        // Kolom Kiri: Kode, Siklus, & Institusi
                        Stack::make([
                            TextColumn::make('code')
                                ->label('Kode Temuan')
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
                        ])->space(1),
                        // Kolom Kanan: Status & Badge
                        Stack::make([
                            TextColumn::make('classification')
                                ->label('Klasifikasi')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'nonconformity' => 'Ketidaksesuaian',
                                    'opportunity' => 'Peluang Perbaikan',
                                    default => 'Observasi',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'nonconformity' => 'danger',
                                    'opportunity' => 'info',
                                    default => 'warning',
                                }),
                            TextColumn::make('severity')
                                ->label('Keparahan')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'major' => 'Mayor',
                                    'minor' => 'Minor',
                                    'low' => 'Rendah',
                                    default => 'Sedang',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'major' => 'danger',
                                    'minor' => 'warning',
                                    'low' => 'gray',
                                    default => 'info',
                                }),
                            TextColumn::make('status')
                                ->label('Status')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'in_progress' => 'Dalam Tindak Lanjut',
                                    'closed' => 'Ditutup',
                                    default => 'Terbuka',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'closed' => 'success',
                                    'in_progress' => 'warning',
                                    default => 'danger',
                                }),
                            TextColumn::make('evidenceLinks.evidence.title')
                                ->label('Bukti Audit')
                                ->badge()
                                ->color('info')
                                ->placeholder('0 Bukti')
                                ->limitList(1)
                                ->expandableLimitedList(),
                            TextColumn::make('created_at')
                                ->label('Dibuat')
                                ->size('xs')
                                ->color('gray')
                                ->dateTime(),
                        ])->space(1),
                    ]),
                    // Teks Kondisi Utuh
                    TextColumn::make('condition')
                        ->label('Kondisi')
                        ->wrap()
                        ->extraAttributes(['class' => 'pt-2 font-medium text-gray-900 dark:text-gray-100']),
                ])->space(3),
            ])
            // contentGrid dihapus agar tidak menjadi double card
            ->filters([
                SelectFilter::make('classification')->label('Klasifikasi')->options(['observation' => 'Observasi', 'nonconformity' => 'Ketidaksesuaian', 'opportunity' => 'Peluang Perbaikan']),
                SelectFilter::make('severity')->label('Keparahan')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'minor' => 'Minor', 'major' => 'Mayor']),
                SelectFilter::make('status')->label('Status')->options(['open' => 'Terbuka', 'in_progress' => 'Dalam Tindak Lanjut', 'closed' => 'Ditutup']),
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
                                'audit_evidence' => 'Bukti Temuan Lapangan',
                                'supporting_evidence' => 'Bukti Pendukung',
                                'policy_document' => 'Dokumen Acuan Standar',
                            ])
                            ->default('audit_evidence')
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('citation_page')
                            ->label('Halaman / Bab Rujukan')
                            ->placeholder('Contoh: Hlm. 12 atau Lampiran 1')
                            ->maxLength(100),
                        \Filament\Forms\Components\TextInput::make('citation_note')
                            ->label('Catatan Keterangan')
                            ->placeholder('Catatan konteks bukti temuan...')
                            ->maxLength(255),
                    ])
                    ->action(function (\App\Models\AmiFinding $record, array $data): void {
                        \App\Models\EvidenceLink::query()->updateOrCreate([
                            'evidence_id' => $data['evidence_id'],
                            'linkable_type' => \App\Models\AmiFinding::class,
                            'linkable_id' => $record->getKey(),
                        ], [
                            'relation_type' => $data['relation_type'],
                            'citation_page' => $data['citation_page'] ?? null,
                            'citation_note' => $data['citation_note'] ?? null,
                            'is_required' => true,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Bukti audit berhasil ditautkan ke temuan AMI.')
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
