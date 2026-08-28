<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtlActions\RelationManagers;

use App\Domain\Quality\RtlEffectivenessReviewService;
use App\Models\Evidence;
use App\Models\SpmiEvaluation;
use App\Support\Ui\StatusLabel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EffectivenessReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'effectivenessReviews';

    protected static ?string $title = 'Tinjauan Efektivitas RTL';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outcome')->label('Hasil Efektivitas')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'effective' => 'Efektif',
                    'partially_effective' => 'Efektif Sebagian',
                    'ineffective' => 'Tidak Efektif',
                    default => $state ?: 'Belum Ditentukan',
                })->badge()->sortable(),
                TextColumn::make('status')->label('Status Review')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('effectiveness_score')->label('Skor Efektivitas')->suffix('/100')->sortable(),
                TextColumn::make('ppepp_stage')->label('Tahap PPEPP')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'control' => 'Pengendalian',
                    'improvement' => 'Peningkatan',
                    default => 'Evaluasi',
                })->badge(),
                TextColumn::make('follow_up_required')->label('Tindak Lanjut')->formatStateUsing(fn ($state): string => $state ? 'Wajib' : 'Tidak Wajib')->badge(),
                TextColumn::make('evidence_links_count')->counts('evidenceLinks')->label('Bukti Outcome'),
                TextColumn::make('reviewer.name')->label('Reviewer'),
                TextColumn::make('reviewed_at')->label('Ditinjau Pada')->dateTime()->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Draf Tinjauan')
                    ->visible(fn (): bool => auth()->user()?->can('review rtl effectiveness') ?? false)
                    ->form([
                        Select::make('outcome')->label('Hasil Efektivitas')->options(['effective' => 'Efektif', 'partially_effective' => 'Efektif Sebagian', 'ineffective' => 'Tidak Efektif'])->required(),
                        TextInput::make('effectiveness_score')->label('Skor Efektivitas')->numeric()->minValue(0)->maxValue(100)->required(),
                        Select::make('ppepp_stage')->label('Tahap PPEPP')->options(['evaluation' => 'Evaluasi', 'control' => 'Pengendalian', 'improvement' => 'Peningkatan'])->default('evaluation')->required(),
                        Select::make('spmi_evaluation_id')->label('Evaluasi SPMI (Opsional)')->options(function (): array {
                            $ptId = $this->getOwnerRecord()->perguruan_tinggi_id;

                            return SpmiEvaluation::query()->with('realization.indicator')->where('perguruan_tinggi_id', $ptId)->latest('id')->get()->mapWithKeys(function (SpmiEvaluation $evaluation): array {
                                $realization = $evaluation->realization;
                                $indicator = $realization?->indicator?->name ?? 'Realisasi tanpa indikator';
                                $year = $realization?->period_year ?? '—';
                                $result = match ($evaluation->result) {
                                    'met' => 'Tercapai',
                                    'partially_met' => 'Tercapai Sebagian',
                                    'not_met' => 'Belum Tercapai',
                                    default => $evaluation->result ?: 'Belum ditentukan',
                                };

                                return [$evaluation->getKey() => sprintf('%s | Tahun %s | %s', $indicator, $year, $result)];
                            })->all();
                        })->searchable()->helperText('Pilih evaluasi berdasarkan indikator dan tahun, bukan ID teknis.'),
                        Textarea::make('observed_result')->label('Hasil yang Diamati')->required()->rows(4),
                        Textarea::make('evidence_summary')->label('Ringkasan Evidence')->rows(3),
                        Textarea::make('recommendation')->label('Rekomendasi')->rows(4),
                        Select::make('follow_up_required')->label('Tindak Lanjut')->options([1 => 'Wajib', 0 => 'Tidak Wajib'])->default(0)->required(),
                    ])
                    ->using(fn (array $data, string $model): object => app(RtlEffectivenessReviewService::class)->createDraft($this->getOwnerRecord(), auth()->user(), $data))
                    ->successNotification(Notification::make()->title('Draf tinjauan efektivitas tersimpan.')->success()),
            ])
            ->recordActions([
                Action::make('attachOutcomeEvidence')->label('Lampirkan Bukti')->icon('heroicon-o-link')->visible(fn ($record): bool => (auth()->user()?->can('review rtl effectiveness') ?? false) && $record->status !== 'approved')->form([
                    Select::make('evidence_id')->label('Evidence Cloud')->options(fn (): array => Evidence::query()->where('perguruan_tinggi_id', $this->getOwnerRecord()->perguruan_tinggi_id)->whereHas('versions', fn ($query) => $query->whereNotNull('external_url'))->orderBy('title')->pluck('title', 'id')->all())->searchable()->required(),
                    TextInput::make('label')->label('Label Bukti')->default('Evidence outcome tinjauan efektivitas'),
                ])->action(function ($record, array $data): void {
                    app(RtlEffectivenessReviewService::class)->attachOutcomeEvidence($record, auth()->user(), (int) $data['evidence_id'], $data['label'] ?? null);
                    Notification::make()->title('Evidence outcome berhasil ditautkan.')->success()->send();
                }),
                Action::make('submit')->label('Kirim')->color('warning')->visible(fn ($record): bool => (auth()->user()?->can('submit rtl effectiveness') ?? false) && $record->status === 'draft')->requiresConfirmation()->action(function ($record): void {
                    app(RtlEffectivenessReviewService::class)->submit($record, auth()->user());
                    Notification::make()->title('Tinjauan efektivitas berhasil dikirim.')->success()->send();
                }),
                Action::make('approve')->label('Setujui')->color('success')->visible(fn ($record): bool => (auth()->user()?->can('approve rtl effectiveness') ?? false) && $record->status === 'submitted')->requiresConfirmation()->action(function ($record): void {
                    app(RtlEffectivenessReviewService::class)->approve($record, auth()->user());
                    Notification::make()->title('Tinjauan efektivitas disetujui dan feedback PPEPP diproses.')->success()->send();
                }),
            ])
            ->toolbarActions([]);
    }
}
