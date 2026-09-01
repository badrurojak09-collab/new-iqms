<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\Pages;

use App\Domain\Accreditation\LedLkpsValidator;
use App\Domain\Accreditation\ReadinessScoringService;
use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Domain\Accreditation\SubmitAccreditation;
use App\Filament\Resources\Accreditations\AccreditationResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditAccreditation extends EditRecord
{
    protected static string $resource = AccreditationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calculateReadiness')
                ->label('Hitung Kesiapan')
                ->icon('heroicon-o-chart-bar')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->requiresConfirmation()
                ->action(function (): void {
                    $run = app(ReadinessScoringService::class)->calculate(auth()->user(), $this->record);
                    Notification::make()
                        ->title('Kesiapan berhasil dihitung')
                        ->body("Skor Terbobot: {$run->weighted_score}% ({$run->ready_items}/{$run->total_items} item siap)")
                        ->success()
                        ->send();
                }),
            Action::make('validateLedLkps')
                ->label('Validasi LED/LKPS')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->action(function (): void {
                    $result = app(LedLkpsValidator::class)->validate($this->record);
                    $notification = Notification::make()->title($result['valid'] ? 'Validasi berhasil' : 'Validasi menemukan masalah');
                    $notification = $result['valid'] ? $notification->success() : $notification->danger();
                    if ($result['errors'] !== []) {
                        $notification->body(collect($result['errors'])->take(5)->map(fn (array $error): string => $error['key'].': '.$error['message'])->implode("\n"));
                    }
                    $notification->send();
                }),
            Action::make('calculateScore')
                ->label('Hitung Skor')
                ->icon('heroicon-o-calculator')
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->requiresConfirmation()
                ->action(function (): void {
                    $snapshot = app(RuntimeScoringEngine::class)->scoreAndPersist($this->record, auth()->id());
                    Notification::make()->title('Snapshot skor berhasil disimpan: '.number_format((float) $snapshot->score, 2))->success()->body('Hash integritas snapshot: '.$snapshot->snapshot_hash)->send();
                }),
            Action::make('submitAccreditation')
                ->label('Ajukan Akreditasi')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Akreditasi ke BAN-PT/LAM')
                ->modalDescription('Pastikan semua respons LED/LKPS berstatus ready dan instrumen sudah published. Paket pengajuan akan dikunci dan dibuat hash integritasnya.')
                ->form([
                    Textarea::make('notes')->label('Catatan Pengajuan')->placeholder('Catatan atau pengantar pengajuan...'),
                ])
                ->visible(fn (): bool => in_array($this->record->status, ['draft', 'in_progress', 'review', 'ready'], true) && (auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation')))
                ->action(function (array $data): void {
                    try {
                        $submission = app(SubmitAccreditation::class)->handle($this->record, (int) auth()->id(), $data['notes'] ?? null);
                        Notification::make()
                            ->title('Akreditasi berhasil diajukan!')
                            ->body('Nomor Pengajuan: #'.$submission->submission_no.' | Hash: '.substr((string) $submission->package_hash, 0, 16).'...')
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Pengajuan Gagal')
                            ->body(collect($e->errors())->flatten()->implode("\n"))
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('exportDocuments')
                ->label('Ekspor Dokumen')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->form([
                    \Filament\Forms\Components\Select::make('document_type')
                        ->label('Pilih Format Dokumen Luaran')
                        ->options([
                            '── Format Native (Siap Sunting) ──' => [
                                'led-docx'  => '📝 Draf LED — Microsoft Word (.docx)',
                                'lkps-xlsx' => '📊 Borang LKPS/LKPT — Microsoft Excel (.xlsx)',
                            ],
                            '── Format Web / Cetak (Preview PDF) ──' => [
                                'led-html'              => '📄 Draf LED — HTML / Cetak / PDF',
                                'lkps-html'             => '🖥️ Tabel LKPS/LKPT — HTML / Cetak',
                                'score-simulation'      => '🏆 Matriks Simulasi Skor & Syarat Perlu',
                                'evidence-matrix-html'  => '📎 Peta Bukti — HTML / Cetak',
                            ],
                            '── Format Data (Impor / Integrasi) ──' => [
                                'lkps-csv'              => '📥 Borang LKPS/LKPT — CSV',
                                'evidence-matrix-csv'   => '📥 Peta Bukti — CSV',
                            ],
                        ])
                        ->required()
                        ->default('led-docx')
                        ->selectablePlaceholder(false),
                ])
                ->action(function (array $data): void {
                    $type = $data['document_type'];
                    $url = route('accreditations.export', ['accreditation' => $this->record->getKey(), 'type' => $type]);
                    $this->js("window.open('{$url}', '_blank');");
                }),
            Action::make('save')->label('Simpan')->submit('save'),
        ];
    }
}
