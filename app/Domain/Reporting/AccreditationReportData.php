<?php

declare(strict_types=1);

namespace App\Domain\Reporting;

use App\Models\Accreditation;
use Illuminate\Support\Collection;

final class AccreditationReportData
{
    /** @return Collection<int, array<string, mixed>> */
    public function forPerguruanTinggi(int $perguruanTinggiId, ?int $programStudiId = null, ?string $from = null, ?string $until = null): Collection
    {
        return Accreditation::query()
            ->with(['programStudi', 'instrumentVersion.family', 'sections', 'responses', 'readinessItems'])
            ->where('perguruan_tinggi_id', $perguruanTinggiId)
            ->when($programStudiId, fn ($query) => $query->where('program_studi_id', $programStudiId))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($until, fn ($query) => $query->whereDate('created_at', '<=', $until))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Accreditation $accreditation): array => [
                'code' => $accreditation->code,
                'title' => $accreditation->title,
                'scope' => $accreditation->scope_type,
                'program_studi' => $accreditation->programStudi?->nama_prodi ?? 'Institusi',
                'instrument_version' => $accreditation->instrumentVersion?->version_label,
                'status' => $accreditation->status,
                'sections' => $accreditation->sections->count(),
                'led_progress' => round((float) ($accreditation->sections->where('section_type', 'led')->avg('readiness_percent') ?? 0), 2),
                'lkps_progress' => round((float) ($accreditation->sections->where('section_type', 'lkps')->avg('readiness_percent') ?? 0), 2),
                'responses' => $accreditation->responses->count(),
                'responses_completed' => $accreditation->responses->whereIn('status', ['submitted', 'verified', 'approved'])->count(),
                'readiness_items' => $accreditation->readinessItems->count(),
                'readiness_completed' => $accreditation->readinessItems->whereIn('status', ['done', 'completed', 'verified'])->count(),
                'planned_submission_date' => $accreditation->planned_submission_date?->format('Y-m-d'),
                'decision_result' => $accreditation->decision_result,
            ]);
    }
}
