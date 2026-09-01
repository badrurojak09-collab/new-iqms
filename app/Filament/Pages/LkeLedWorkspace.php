<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Accreditation\ReadinessScoringService;
use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Filament\Resources\Accreditations\AccreditationResource;
use App\Models\Accreditation;
use App\Models\AccreditationResponse;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;

final class LkeLedWorkspace extends Page
{
    protected static ?string $navigationLabel = 'Workspace LKE/LED';
    protected static ?string $title = 'Workspace LKE dan LED';
    protected static \UnitEnum|string|null $navigationGroup = 'Akreditasi';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.lke-led-workspace';

    public ?int $accreditationId = null;
    public ?int $selectedSectionId = null;
    public string $selectedStatus = 'all';
    public string $search = '';

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
                'perguruanTinggi',
                'programStudi',
                'sections.instrumentNode',
                'responses.section',
                'responses.instrumentNode',
                'responses.evidenceLinks.evidence',
                'readinessItems',
                'submissions',
                'readinessRuns' => fn ($q) => $q->latest('id'),
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
    }

    public function filterBySection(?int $sectionId): void
    {
        $this->selectedSectionId = $sectionId;
    }

    public function filterByStatus(string $status): void
    {
        $this->selectedStatus = $status;
    }

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
        ];
    }
}
