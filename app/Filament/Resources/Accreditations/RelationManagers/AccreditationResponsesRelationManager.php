<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use App\Domain\Accreditation\AccreditationResponseWorkflowService;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('accreditation_section_id')->label('Bagian')->options(fn (): array => AccreditationSection::query()->where('accreditation_id', $this->ownerRecord->getKey())->orderBy('sort_order')->pluck('title', 'id')->all())->searchable()->preload()->required(),
            TextInput::make('response_key')->label('Kunci Respons')->required()->maxLength(120),
            Select::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'numeric' => 'Angka', 'json' => 'JSON'])->required()->default('text'),
            Textarea::make('response_text')->label('Narasi/Jawaban')->columnSpanFull(),
            TextInput::make('response_numeric')->label('Nilai Angka')->numeric(),
            Hidden::make('status')->default(AccreditationResponse::STATUS_DRAFT),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('response_key')->label('Kunci Respons')->searchable()->sortable()->copyable(),
            TextColumn::make('section.title')->label('Bagian')->wrap(),
            TextColumn::make('instrumentNode.title')->label('Elemen Instrumen')->wrap()->placeholder('Belum dipetakan'),
            TextColumn::make('response_type')->label('Jenis Respons')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'text' => 'Teks',
                'numeric' => 'Angka',
                'json' => 'JSON',
                default => (string) $state,
            }),
            TextColumn::make('status')->label('Status Respons')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('evidenceLinks.evidence.title')
                ->label('Bukti Tertaut')
                ->badge()
                ->color('info')
                ->placeholder('Belum ada bukti')
                ->limitList(2)
                ->expandableLimitedList(),
            TextColumn::make('revision_no')->label('Revisi')->sortable()->default(1),
            TextColumn::make('submitted_at')->label('Dikirim Pada')->dateTime()->sortable(),
        ])->recordActions([
            Action::make('linkEvidence')
                ->label('Tautkan Bukti')
                ->icon('heroicon-o-paper-clip')
                ->color('info')
                ->form([
                    Select::make('evidence_id')
                        ->label('Pilih Dokumen Bukti (Evidence Cloud)')
                        ->options(function (): array {
                            $ptId = $this->ownerRecord->perguruan_tinggi_id;

                            return Evidence::query()
                                ->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))
                                ->orderBy('title')
                                ->pluck('title', 'id')
                                ->all();
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('relation_type')
                        ->label('Jenis Bukti')
                        ->options([
                            'primary_evidence' => 'Bukti Utama',
                            'supporting_evidence' => 'Bukti Pendukung',
                            'policy_document' => 'Dokumen Kebijakan/SK',
                            'evaluation_report' => 'Laporan Evaluasi/Audit',
                        ])
                        ->default('primary_evidence')
                        ->required(),
                    TextInput::make('citation_page')
                        ->label('Halaman / Bab Rujukan')
                        ->placeholder('Contoh: Hlm. 12-15 atau Bab 3.2')
                        ->maxLength(100),
                    TextInput::make('citation_note')
                        ->label('Catatan Sitasi / Keterangan')
                        ->placeholder('Keterangan konteks bukti...')
                        ->maxLength(255),
                    Toggle::make('is_required')
                        ->label('Wajib untuk Validasi LED/LKPS')
                        ->default(true),
                ])
                ->action(function (AccreditationResponse $record, array $data): void {
                    EvidenceLink::query()->updateOrCreate([
                        'evidence_id' => $data['evidence_id'],
                        'linkable_type' => AccreditationResponse::class,
                        'linkable_id' => $record->getKey(),
                    ], [
                        'relation_type' => $data['relation_type'],
                        'citation_page' => $data['citation_page'] ?? null,
                        'citation_note' => $data['citation_note'] ?? null,
                        'is_required' => (bool) ($data['is_required'] ?? false),
                    ]);

                    Notification::make()
                        ->title('Bukti berhasil ditautkan ke respons')
                        ->success()
                        ->send();
                }),
            Action::make('submit')
                ->label('Kirim Review')
                ->requiresConfirmation()
                ->visible(fn (AccreditationResponse $record): bool => in_array($record->status, [AccreditationResponse::STATUS_DRAFT, AccreditationResponse::STATUS_REVISION_REQUIRED, AccreditationResponse::STATUS_REJECTED], true))
                ->action(fn (AccreditationResponse $record): AccreditationResponse => app(AccreditationResponseWorkflowService::class)->submit($record, auth()->user())),
            Action::make('startReview')
                ->label('Mulai Review')
                ->requiresConfirmation()
                ->visible(fn (AccreditationResponse $record): bool => $record->status === AccreditationResponse::STATUS_SUBMITTED)
                ->action(fn (AccreditationResponse $record): AccreditationResponse => app(AccreditationResponseWorkflowService::class)->startReview($record, auth()->user())),
            Action::make('requestRevision')
                ->label('Minta Revisi')
                ->form([Textarea::make('notes')->label('Catatan Revisi')->required()->minLength(5)])
                ->visible(fn (AccreditationResponse $record): bool => in_array($record->status, [AccreditationResponse::STATUS_SUBMITTED, AccreditationResponse::STATUS_IN_REVIEW], true))
                ->action(fn (AccreditationResponse $record, array $data): AccreditationResponse => app(AccreditationResponseWorkflowService::class)->requestRevision($record, auth()->user(), $data['notes'])),
            Action::make('approve')
                ->label('Setujui')
                ->requiresConfirmation()
                ->visible(fn (AccreditationResponse $record): bool => $record->status === AccreditationResponse::STATUS_IN_REVIEW)
                ->action(fn (AccreditationResponse $record): AccreditationResponse => app(AccreditationResponseWorkflowService::class)->approve($record, auth()->user())),
            Action::make('lock')
                ->label('Kunci Respons')
                ->requiresConfirmation()
                ->visible(fn (AccreditationResponse $record): bool => $record->status === AccreditationResponse::STATUS_APPROVED)
                ->action(fn (AccreditationResponse $record): AccreditationResponse => app(AccreditationResponseWorkflowService::class)->lock($record, auth()->user())),
        ])->defaultSort('updated_at', 'desc');
    }
}
