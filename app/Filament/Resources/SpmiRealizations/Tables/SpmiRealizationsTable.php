<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Tables;

use App\Domain\Spmi\EvaluateSpmiRealization;
use App\Domain\Spmi\SubmitSpmiRealization;
use App\Domain\Spmi\VerifySpmiRealization;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\SpmiRealization;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;

class SpmiRealizationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['indicator', 'perguruanTinggi', 'programStudi', 'evidenceLinks.evidence']))
            ->columns([
                TextColumn::make('indicator.name')->label('Indikator')->searchable()->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('—')->searchable(),
                TextColumn::make('period_year')->label('Tahun')->sortable(),
                TextColumn::make('realization_numeric')->label('Realisasi Numerik')->placeholder('—'),
                TextColumn::make('evidenceLinks.evidence.title')
                    ->label('Bukti Capaian')
                    ->badge()
                    ->color('info')
                    ->placeholder('Belum ada bukti')
                    ->limitList(1)
                    ->expandableLimitedList(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            ])
            ->filters([TrashedFilter::make()->label('Data Terhapus')])
            ->recordActions([
                RestoreAction::make()->label('Pulihkan')->visible(fn ($record): bool => $record->trashed()),
                ForceDeleteAction::make()->label('Hapus Permanen')->visible(fn ($record): bool => $record->trashed()),
                EditAction::make()->label('Edit'),
                Action::make('linkEvidence')
                    ->label('Tautkan Bukti')
                    ->icon('heroicon-o-paper-clip')
                    ->color('info')
                    ->visible(fn ($record): bool => (auth()->user()?->can('manage spmi') ?? false) && $record->status !== 'rejected')
                    ->form([
                        Select::make('evidence_id')
                            ->label('Pilih Dokumen Bukti (Evidence Cloud)')
                            ->options(function ($record): array {
                                $ptId = $record->perguruan_tinggi_id;

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
                            ->helperText('Hanya evidence dengan versi tautan cloud yang tersedia yang ditampilkan.'),
                        Select::make('relation_type')
                            ->label('Jenis Bukti')
                            ->options([
                                'supports' => 'Bukti Capaian Utama',
                                'supporting_evidence' => 'Bukti Pendukung',
                                'activity_report' => 'Laporan Kegiatan / Dokumentasi',
                                'policy_document' => 'Dokumen / SK Pendukung',
                            ])
                            ->default('supports')
                            ->required(),
                        TextInput::make('citation_page')
                            ->label('Halaman / Bab Rujukan')
                            ->placeholder('Contoh: Hlm. 5-8 atau Lampiran 2')
                            ->maxLength(100),
                        TextInput::make('citation_note')
                            ->label('Catatan Keterangan')
                            ->placeholder('Catatan konteks pencapaian...')
                            ->maxLength(255),
                    ])
                    ->action(function (SpmiRealization $record, array $data): void {
                        EvidenceLink::query()->updateOrCreate([
                            'evidence_id' => $data['evidence_id'],
                            'linkable_type' => SpmiRealization::class,
                            'linkable_id' => $record->getKey(),
                        ], [
                            'relation_type' => $data['relation_type'],
                            'citation_page' => $data['citation_page'] ?? null,
                            'citation_note' => $data['citation_note'] ?? null,
                            'is_required' => true,
                        ]);

                        Notification::make()
                            ->title('Bukti cloud berhasil ditautkan ke realisasi SPMI.')
                            ->success()
                            ->send();
                    }),
                Action::make('ajukan')->label('Ajukan')->color('info')->icon('heroicon-o-paper-airplane')
                    ->visible(fn ($record): bool => in_array($record->status, ['draft', 'rejected'], true) && (auth()->user()?->can('manage spmi') ?? false))
                    ->requiresConfirmation()->action(function ($record): void {
                        app(SubmitSpmiRealization::class)->handle($record);
                        Notification::make()->title('Realisasi berhasil diajukan.')->success()->send();
                    }),
                Action::make('verifikasi')->label('Verifikasi')->color('success')->icon('heroicon-o-check-badge')
                    ->visible(fn ($record): bool => in_array($record->status, ['submitted', 'draft'], true) && (auth()->user()?->can('manage spmi') ?? false))
                    ->form([Textarea::make('notes')->label('Catatan Verifikasi')->rows(3)])
                    ->action(function ($record, array $data): void {
                        app(VerifySpmiRealization::class)->handle($record, (int) auth()->id(), $data['notes'] ?? null);
                        Notification::make()->title('Realisasi berhasil diverifikasi.')->success()->send();
                    }),
                Action::make('evaluasi_otomatis')->label('Evaluasi Otomatis')->color('warning')->icon('heroicon-o-calculator')
                    ->visible(fn ($record): bool => $record->status === 'verified' && (auth()->user()?->can('manage spmi') ?? false))
                    ->form([
                        Textarea::make('analysis')->label('Analisis Evaluasi')->required()->rows(4),
                        Textarea::make('root_cause')->label('Akar Masalah')->rows(3),
                        Textarea::make('recommendation')->label('Rekomendasi')->rows(3),
                    ])->action(function ($record, array $data): void {
                        app(EvaluateSpmiRealization::class)->handle($record, (int) auth()->id(), $data['analysis'], $data['root_cause'] ?? null, $data['recommendation'] ?? null);
                        Notification::make()->title('Evaluasi otomatis berhasil dihitung.')->success()->send();
                    }),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
