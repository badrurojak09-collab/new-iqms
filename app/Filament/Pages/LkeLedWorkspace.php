<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Accreditation\AccreditationResponseWorkflowService;
use App\Domain\Accreditation\ReadinessScoringService;
use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Domain\Lkps\LkpsDatasetService;
use App\Domain\Lkps\LkpsImportService;
use App\Filament\Resources\Accreditations\AccreditationResource;
use App\Models\Accreditation;
use App\Models\AccreditationResponse;
use App\Models\AccreditationResponseRevision;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use App\Models\LkpsDataset;
use App\Models\LkpsTemplate;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LkeLedWorkspace extends Page
{
    use WithFileUploads;

    protected static ?string $navigationLabel = 'Workspace LKE/LED';
    protected static ?string $title = 'Workspace LKE dan LED';
    protected static \UnitEnum|string|null $navigationGroup = 'Akreditasi';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.lke-led-workspace';

    public ?int $accreditationId = null;
    public string $activeWorkspaceTab = 'led'; // 'led' or 'lkps'

    // LED state
    public ?int $selectedSectionId = null;
    public string $selectedStatus = 'all';
    public string $search = '';

    // Edit Narrative Modal State
    public bool $showEditResponseModal = false;
    public ?int $editingResponseId = null;
    public string $editingResponseKey = '';
    public string $editingResponseTitle = '';
    public string $editingResponseRequirement = '';
    public string $editingResponseGuidance = '';
    public string $editingResponseText = '';
    public string $editingResponseStatus = '';
    public int $editingRevisionNo = 1;

    // Revision Request Modal State
    public bool $showRevisionModal = false;
    public ?int $revisionResponseId = null;
    public string $revisionResponseKey = '';
    public string $revisionNotes = '';

    // Revision History Modal State
    public bool $showHistoryModal = false;
    public ?int $historyResponseId = null;
    public string $historyResponseKey = '';
    /** @var array<int, array<string, mixed>> */
    public array $historyRevisions = [];

    // Evidence Citation Modal State
    public bool $showEvidenceModal = false;
    public ?int $evidenceModalResponseId = null;
    public string $evidenceModalResponseKey = '';
    public ?int $selectedEvidenceId = null;
    public ?int $citationPage = 1;
    public string $citationNote = '';
    public bool $citationIsRequired = true;

    // LKPS state
    public ?int $selectedLkpsTemplateId = null;
    public array $lkpsRows = [];
    public array $lkpsErrors = [];
    public array $lkpsSummary = [];

    // Import state
    public $importFile = null;
    public ?array $importPreview = null;
    public bool $showImportModal = false;

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $paramId = request()->integer('accreditation') ?: null;
        if ($paramId !== null && AccreditationResource::getEloquentQuery()->whereKey($paramId)->exists()) {
            $this->accreditationId = $paramId;
        } else {
            $first = $this->getAccreditations()->first();
            $this->accreditationId = $first?->getKey();
        }

        $this->initLkpsWorkspace();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user instanceof User
            && ($user->can('view accreditation') || $user->can('manage accreditation') || $user->can('review accreditation'));
    }

    public function getHeading(): string|Htmlable
    {
        return 'Workspace LKE dan LED';
    }

    /** @return Collection<int, Accreditation> */
    public function getAccreditations(): Collection
    {
        return AccreditationResource::getEloquentQuery()
            ->with([
                'instrumentVersion.family.accreditationBody',
                'perguruanTinggi',
                'programStudi',
                'responses',
            ])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function getSelectedAccreditation(): ?Accreditation
    {
        if ($this->accreditationId === null) {
            return null;
        }

        return AccreditationResource::getEloquentQuery()
            ->with([
                'instrumentVersion.family.accreditationBody',
                'instrumentVersion.assessmentCriteria',
                'instrumentVersion.lkpsTemplates.columns',
                'perguruanTinggi',
                'programStudi',
                'sections.instrumentNode',
                'responses.section',
                'responses.instrumentNode',
                'responses.evidenceLinks.evidence',
                'readinessItems',
                'submissions',
                'readinessRuns' => fn ($q) => $q->latest('id'),
                'lkpsDatasets.template.columns',
            ])
            ->find($this->accreditationId);
    }

    public function selectAccreditation(int $id): void
    {
        abort_unless(AccreditationResource::getEloquentQuery()->whereKey($id)->exists(), 404);
        $this->accreditationId = $id;
        $this->selectedSectionId = null;
        $this->selectedStatus = 'all';
        $this->search = '';
        $this->selectedLkpsTemplateId = null;
        $this->closeAllModals();
        $this->initLkpsWorkspace();
    }

    public function setWorkspaceTab(string $tab): void
    {
        $this->activeWorkspaceTab = $tab;
        if ($tab === 'lkps' && empty($this->lkpsRows)) {
            $this->initLkpsWorkspace();
        }
    }

    public function filterBySection(?int $sectionId): void
    {
        $this->selectedSectionId = $sectionId;
    }

    public function filterByStatus(string $status): void
    {
        $this->selectedStatus = $status;
    }

    // ── Response Narrative Edit & Workflow Methods ─────────────

    public function openEditResponseModal(int $responseId): void
    {
        $response = AccreditationResponse::query()
            ->with(['section.instrumentNode', 'instrumentNode', 'evidenceLinks.evidence'])
            ->find($responseId);

        if (! $response) {
            return;
        }

        $node = $response->instrumentNode ?: $response->section?->instrumentNode;

        $this->editingResponseId = $response->getKey();
        $this->editingResponseKey = (string) $response->response_key;
        $this->editingResponseTitle = (string) ($response->section?->title ?: $response->response_key);
        $this->editingResponseRequirement = (string) ($node?->requirement ?? '');
        $this->editingResponseGuidance = (string) ($node?->guidance ?? '');
        $this->editingResponseText = (string) ($response->response_text ?? '');
        $this->editingResponseStatus = (string) $response->status;
        $this->editingRevisionNo = (int) ($response->revision_no ?: 1);
        $this->showEditResponseModal = true;
    }

    public function closeEditResponseModal(): void
    {
        $this->showEditResponseModal = false;
        $this->editingResponseId = null;
        $this->editingResponseText = '';
    }

    public function saveResponseNarrative(): void
    {
        if (! $this->editingResponseId) {
            return;
        }

        $response = AccreditationResponse::query()->find($this->editingResponseId);
        if (! $response) {
            return;
        }

        if ($response->isLocked()) {
            Notification::make()->title('Butir Terkunci')->body('Butir respons ini telah dikunci dan tidak dapat diubah.')->danger()->send();
            return;
        }

        $actor = auth()->user();
        $workflow = app(AccreditationResponseWorkflowService::class);

        try {
            $workflow->revise($response, $actor, [
                'response_text' => $this->editingResponseText,
            ], 'Pembaruan narasi respons LED di Workspace');

            $this->closeEditResponseModal();

            Notification::make()
                ->title('Narasi Berhasil Disimpan')
                ->body("Respons butir {$response->response_key} berhasil diperbarui (Rev #{$response->refresh()->revision_no}).")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Menyimpan Narasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitResponse(int $responseId): void
    {
        $response = AccreditationResponse::query()->find($responseId);
        if (! $response) {
            return;
        }

        $actor = auth()->user();
        try {
            app(AccreditationResponseWorkflowService::class)->submit($response, $actor);
            Notification::make()
                ->title('Respons Berhasil Diajukan')
                ->body("Butir {$response->response_key} telah diajukan untuk peninjauan (In Review).")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Aksi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function startReviewResponse(int $responseId): void
    {
        $response = AccreditationResponse::query()->find($responseId);
        if (! $response) {
            return;
        }

        $actor = auth()->user();
        try {
            app(AccreditationResponseWorkflowService::class)->startReview($response, $actor);
            Notification::make()
                ->title('Proses Review Dimulai')
                ->body("Butir {$response->response_key} kini dalam status telaah.")
                ->info()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Aksi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function openRevisionModal(int $responseId): void
    {
        $response = AccreditationResponse::query()->find($responseId);
        if (! $response) {
            return;
        }

        $this->revisionResponseId = $response->getKey();
        $this->revisionResponseKey = (string) $response->response_key;
        $this->revisionNotes = (string) ($response->review_notes ?? '');
        $this->showRevisionModal = true;
    }

    public function closeRevisionModal(): void
    {
        $this->showRevisionModal = false;
        $this->revisionResponseId = null;
        $this->revisionNotes = '';
    }

    public function submitRevisionRequest(): void
    {
        if (! $this->revisionResponseId) {
            return;
        }

        $response = AccreditationResponse::query()->find($this->revisionResponseId);
        if (! $response) {
            return;
        }

        $actor = auth()->user();
        try {
            app(AccreditationResponseWorkflowService::class)->requestRevision($response, $actor, $this->revisionNotes);
            $this->closeRevisionModal();

            Notification::make()
                ->title('Permintaan Revisi Dikirim')
                ->body("Catatan perbaikan telah dicatat untuk butir {$response->response_key}.")
                ->warning()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Aksi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function approveResponse(int $responseId): void
    {
        $response = AccreditationResponse::query()->find($responseId);
        if (! $response) {
            return;
        }

        $actor = auth()->user();
        try {
            app(AccreditationResponseWorkflowService::class)->approve($response, $actor);
            Notification::make()
                ->title('Respons Disetujui')
                ->body("Butir {$response->response_key} telah disetujui.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Aksi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function lockResponse(int $responseId): void
    {
        $response = AccreditationResponse::query()->find($responseId);
        if (! $response) {
            return;
        }

        $actor = auth()->user();
        try {
            app(AccreditationResponseWorkflowService::class)->lock($response, $actor);
            Notification::make()
                ->title('Respons Dikunci')
                ->body("Butir {$response->response_key} telah dikunci secara resmi.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Aksi Gagal')->body($e->getMessage())->danger()->send();
        }
    }

    public function openRevisionHistoryModal(int $responseId): void
    {
        $response = AccreditationResponse::query()
            ->with(['revisions.changer'])
            ->find($responseId);

        if (! $response) {
            return;
        }

        $this->historyResponseId = $response->getKey();
        $this->historyResponseKey = (string) $response->response_key;
        $this->historyRevisions = $response->revisions->map(fn (AccreditationResponseRevision $rev) => [
            'revision_no' => $rev->revision_no,
            'status' => $rev->status,
            'response_text' => $rev->response_text,
            'change_reason' => $rev->change_reason,
            'changed_by_name' => $rev->changer?->name ?? 'Sistem',
            'changed_at' => $rev->changed_at?->translatedFormat('d F Y H:i') ?? '-',
        ])->all();

        $this->showHistoryModal = true;
    }

    public function closeRevisionHistoryModal(): void
    {
        $this->showHistoryModal = false;
        $this->historyResponseId = null;
        $this->historyRevisions = [];
    }

    // ── Evidence Citation Methods ──────────────────────────────

    public function openEvidenceLinkModal(int $responseId): void
    {
        $response = AccreditationResponse::query()->with('evidenceLinks.evidence')->find($responseId);
        if (! $response) {
            return;
        }

        $this->evidenceModalResponseId = $response->getKey();
        $this->evidenceModalResponseKey = (string) $response->response_key;
        $this->selectedEvidenceId = null;
        $this->citationPage = 1;
        $this->citationNote = '';
        $this->citationIsRequired = true;
        $this->showEvidenceModal = true;
    }

    public function closeEvidenceLinkModal(): void
    {
        $this->showEvidenceModal = false;
        $this->evidenceModalResponseId = null;
        $this->selectedEvidenceId = null;
        $this->citationNote = '';
    }

    public function attachEvidenceLink(): void
    {
        if (! $this->evidenceModalResponseId || ! $this->selectedEvidenceId) {
            Notification::make()->title('Pilih Dokumen Bukti')->body('Silakan pilih bukti dari daftar Evidence Center.')->warning()->send();
            return;
        }

        $response = AccreditationResponse::query()->find($this->evidenceModalResponseId);
        if (! $response) {
            return;
        }

        EvidenceLink::query()->updateOrCreate(
            [
                'evidence_id' => $this->selectedEvidenceId,
                'linkable_type' => AccreditationResponse::class,
                'linkable_id' => $response->getKey(),
            ],
            [
                'relation_type' => 'supporting',
                'citation_page' => $this->citationPage ?: 1,
                'citation_note' => $this->citationNote ?: 'Tautan dokumen bukti pendukung.',
                'is_required' => $this->citationIsRequired,
            ]
        );

        Notification::make()
            ->title('Bukti Berhasil Ditautkan')
            ->body("Dokumen bukti berhasil dikaitkan ke butir {$response->response_key}.")
            ->success()
            ->send();

        $this->selectedEvidenceId = null;
        $this->citationNote = '';
    }

    public function detachEvidenceLink(int $evidenceLinkId): void
    {
        EvidenceLink::query()->whereKey($evidenceLinkId)->delete();

        Notification::make()
            ->title('Tautan Bukti Dihapus')
            ->body('Dokumen bukti telah dilepas dari butir ini.')
            ->info()
            ->send();
    }

    /** @return Collection<int, Evidence> */
    public function getAvailableEvidences(): Collection
    {
        $selected = $this->getSelectedAccreditation();
        if (! $selected) {
            return collect();
        }

        return Evidence::query()
            ->where('perguruan_tinggi_id', $selected->perguruan_tinggi_id)
            ->orderBy('title')
            ->get();
    }

    private function closeAllModals(): void
    {
        $this->showEditResponseModal = false;
        $this->showRevisionModal = false;
        $this->showHistoryModal = false;
        $this->showEvidenceModal = false;
        $this->showImportModal = false;
    }

    // ── LKPS Methods ───────────────────────────────────────────

    public function initLkpsWorkspace(): void
    {
        $selected = $this->getSelectedAccreditation();
        if (! $selected || ! $selected->instrumentVersion) {
            return;
        }

        $templates = $selected->instrumentVersion->lkpsTemplates()->with('columns')->orderBy('sort_order')->get();
        if ($templates->isNotEmpty()) {
            if ($this->selectedLkpsTemplateId === null || ! $templates->contains('id', $this->selectedLkpsTemplateId)) {
                $this->selectedLkpsTemplateId = $templates->first()->getKey();
            }
            $this->loadLkpsDataset();
        } else {
            $this->lkpsRows = [];
            $this->lkpsErrors = [];
            $this->lkpsSummary = [];
        }
    }

    public function selectLkpsTemplate(int $templateId): void
    {
        $this->selectedLkpsTemplateId = $templateId;
        $this->loadLkpsDataset();
    }

    public function loadLkpsDataset(): void
    {
        $selected = $this->getSelectedAccreditation();
        if (! $selected || ! $this->selectedLkpsTemplateId) {
            return;
        }

        $template = LkpsTemplate::query()->with('columns')->find($this->selectedLkpsTemplateId);
        if (! $template) {
            return;
        }

        $datasetService = app(LkpsDatasetService::class);
        $dataset = $datasetService->getOrCreateDataset($selected, $template);

        $this->lkpsRows = $dataset->rows_data ?? [];
        $this->lkpsSummary = $dataset->summary_metrics ?? [];
        $this->lkpsErrors = $dataset->validation_errors ?? [];
    }

    public function addLkpsRow(): void
    {
        $template = $this->getSelectedLkpsTemplate();
        if (! $template) {
            return;
        }

        $newRow = [];
        foreach ($template->columns as $col) {
            $newRow[$col->column_key] = '';
        }

        $this->lkpsRows[] = $newRow;
    }

    public function removeLkpsRow(int $index): void
    {
        if (isset($this->lkpsRows[$index])) {
            unset($this->lkpsRows[$index]);
            $this->lkpsRows = array_values($this->lkpsRows);
        }
    }

    public function saveLkpsDataset(): void
    {
        $selected = $this->getSelectedAccreditation();
        $template = $this->getSelectedLkpsTemplate();

        if (! $selected || ! $template) {
            return;
        }

        try {
            $datasetService = app(LkpsDatasetService::class);
            $dataset = $datasetService->saveDataset($selected, $template, $this->lkpsRows, auth()->id());

            $this->lkpsRows = $dataset->rows_data ?? [];
            $this->lkpsSummary = $dataset->summary_metrics ?? [];
            $this->lkpsErrors = $dataset->validation_errors ?? [];

            if (empty($this->lkpsErrors)) {
                Notification::make()
                    ->title('Data LKPS Berhasil Disimpan')
                    ->body("Tabel '{$template->name}' berhasil divalidasi dan disimpan.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Tersimpan dengan Catatan Validasi')
                    ->body('Data berhasil disimpan, namun ada beberapa sel yang memerlukan koreksi.')
                    ->warning()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal Menyimpan Data LKPS')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getSelectedLkpsTemplate(): ?LkpsTemplate
    {
        if (! $this->selectedLkpsTemplateId) {
            return null;
        }

        return LkpsTemplate::query()->with('columns')->find($this->selectedLkpsTemplateId);
    }

    // ── LKPS Import Methods ────────────────────────────────────

    public function openImportModal(): void
    {
        $this->importFile = null;
        $this->importPreview = null;
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->importFile = null;
        $this->importPreview = null;
        $this->showImportModal = false;
    }

    public function updatedImportFile(): void
    {
        $this->previewImport();
    }

    public function previewImport(): void
    {
        if (! $this->importFile) {
            return;
        }

        $template = $this->getSelectedLkpsTemplate();
        if (! $template) {
            return;
        }

        try {
            $importService = app(LkpsImportService::class);
            $this->importPreview = $importService->parseAndReconcile($this->importFile, $template);
        } catch (\Throwable $e) {
            $this->importPreview = null;
            Notification::make()
                ->title('Gagal Membaca File Spreadsheet')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function commitImport(): void
    {
        if (! $this->importPreview || empty($this->importPreview['validation']['rows'])) {
            return;
        }

        $selected = $this->getSelectedAccreditation();
        $template = $this->getSelectedLkpsTemplate();
        if (! $selected || ! $template) {
            return;
        }

        $importService = app(LkpsImportService::class);
        $dataset = $importService->commitImport($selected, $template, $this->importPreview['validation']['rows'], auth()->id());

        $this->lkpsRows = $dataset->rows_data ?? [];
        $this->lkpsSummary = $dataset->summary_metrics ?? [];
        $this->lkpsErrors = $dataset->validation_errors ?? [];

        $this->closeImportModal();

        Notification::make()
            ->title('Import Spreadsheet Berhasil')
            ->body(count($this->lkpsRows) . " baris data berhasil dimuat ke tabel '{$template->name}'.")
            ->success()
            ->send();
    }

    public function exportLkpsTemplateCsv(): StreamedResponse
    {
        $template = $this->getSelectedLkpsTemplate();
        abort_unless($template, 404);

        $columns = $template->columns->sortBy('sort_order');
        $headers = $columns->map(fn ($c) => $c->label . ($c->unit ? " ({$c->unit})" : ''))->all();

        return response()->streamDownload(function () use ($headers, $columns): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            // Example empty row
            $example = $columns->map(fn ($c) => $c->data_type === 'integer' ? '0' : ($c->data_type === 'decimal' ? '0.00' : ''))->all();
            fputcsv($handle, $example);
            fclose($handle);
        }, 'template-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $template->code)) . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    // ── Calculation & Metrics ──────────────────────────────────

    public function calculateReadiness(): void
    {
        $selected = $this->getSelectedAccreditation();
        if (! $selected) {
            return;
        }

        $run = app(ReadinessScoringService::class)->calculate(auth()->user(), $selected);
        Notification::make()
            ->title('Kesiapan Berhasil Dihitung')
            ->body("Skor Terbobot: {$run->weighted_score}% ({$run->ready_items}/{$run->total_items} item siap)")
            ->success()
            ->send();
    }

    public function calculateScore(): void
    {
        $selected = $this->getSelectedAccreditation();
        if (! $selected) {
            return;
        }

        $snapshot = app(RuntimeScoringEngine::class)->scoreAndPersist($selected, auth()->id());
        Notification::make()
            ->title('Simulasi Skor Berhasil')
            ->body('Skor Akhir: ' . number_format((float) $snapshot->score, 2) . ' | Status: ' . strtoupper($snapshot->status))
            ->success()
            ->send();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $selected = $this->getSelectedAccreditation();
        $allResponses = $selected?->responses ?? collect();

        // Calculate metrics
        $responseCount = $allResponses->count();
        $completedResponseCount = $allResponses->whereIn('status', ['approved', 'locked'])->count();
        $inReviewCount = $allResponses->where('status', 'in_review')->count();
        $draftCount = $allResponses->whereIn('status', ['draft', 'revision_required'])->count();
        $completenessPct = $responseCount > 0 ? (int) round(($completedResponseCount / $responseCount) * 100) : 0;

        $evidenceLinksCount = $allResponses->sum(fn (AccreditationResponse $r): int => $r->evidenceLinks->count());
        $verifiedEvidenceCount = $allResponses->sum(fn (AccreditationResponse $r): int => $r->evidenceLinks->where('evidence.status', 'verified')->count());

        $latestReadiness = $selected?->readinessRuns?->first();
        $readinessScore = $latestReadiness?->weighted_score !== null ? (float) $latestReadiness->weighted_score : null;

        // Sections for tab filtering
        $sections = $selected?->sections?->sortBy('sort_order') ?? collect();

        // LKPS Templates
        $lkpsTemplates = $selected?->instrumentVersion?->lkpsTemplates()->with(['columns', 'datasets' => fn ($q) => $q->where('accreditation_id', $selected->getKey())])->orderBy('sort_order')->get() ?? collect();
        $lkpsProgress = $selected ? app(LkpsDatasetService::class)->calculateOverallLkpsProgress($selected) : 0.0;

        // Apply filters to responses
        $filteredResponses = $allResponses;
        if ($this->selectedSectionId !== null) {
            $filteredResponses = $filteredResponses->where('accreditation_section_id', $this->selectedSectionId);
        }
        if ($this->selectedStatus !== 'all') {
            $filteredResponses = $filteredResponses->where('status', $this->selectedStatus);
        }
        if (trim($this->search) !== '') {
            $q = mb_strtolower(trim($this->search));
            $filteredResponses = $filteredResponses->filter(function (AccreditationResponse $r) use ($q): bool {
                return str_contains(mb_strtolower((string) $r->response_key), $q)
                    || str_contains(mb_strtolower((string) $r->response_text), $q)
                    || str_contains(mb_strtolower((string) ($r->section?->title ?? '')), $q);
            });
        }

        $activeResponseForEvidence = $this->evidenceModalResponseId
            ? AccreditationResponse::query()->with('evidenceLinks.evidence')->find($this->evidenceModalResponseId)
            : null;

        return [
            'accreditations' => $this->getAccreditations(),
            'selectedAccreditation' => $selected,
            'responseCount' => $responseCount,
            'completedResponseCount' => $completedResponseCount,
            'inReviewCount' => $inReviewCount,
            'draftCount' => $draftCount,
            'completenessPct' => $completenessPct,
            'evidenceLinksCount' => $evidenceLinksCount,
            'verifiedEvidenceCount' => $verifiedEvidenceCount,
            'readinessScore' => $readinessScore,
            'sections' => $sections,
            'filteredResponses' => $filteredResponses->sortBy('response_key'),
            'lkpsTemplates' => $lkpsTemplates,
            'selectedLkpsTemplate' => $this->getSelectedLkpsTemplate(),
            'lkpsProgress' => $lkpsProgress,
            'availableEvidences' => $this->getAvailableEvidences(),
            'activeResponseForEvidence' => $activeResponseForEvidence,
        ];
    }
}
