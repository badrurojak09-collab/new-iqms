<?php

declare(strict_types=1);

namespace App\Domain\Reporting;

use App\Models\Accreditation;
use App\Models\AccreditationReadinessItem;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\AmiFinding;
use App\Models\InstrumentMapping;
use App\Models\RtlAction;
use App\Models\SpmiEvaluation;

final class QualityDashboardMetrics
{
    /** @return array<string, int|float> */
    public function forPerguruanTinggi(int $perguruanTinggiId): array
    {
        $evaluations = SpmiEvaluation::query()->where('perguruan_tinggi_id', $perguruanTinggiId);
        $totalEvaluations = (clone $evaluations)->count();
        $metEvaluations = (clone $evaluations)->where('result', 'met')->count();

        $accreditations = Accreditation::query()->where('perguruan_tinggi_id', $perguruanTinggiId);
        $totalAccreditations = (clone $accreditations)->count();
        $readyAccreditations = (clone $accreditations)->whereIn('status', ['ready', 'submitted', 'assessment', 'decision', 'closed'])->count();

        $accreditationIds = (clone $accreditations)->pluck('id');
        $sections = AccreditationSection::query()->whereIn('accreditation_id', $accreditationIds);
        $totalSections = (clone $sections)->count();
        $ledSections = (clone $sections)->where('section_type', 'led');
        $lkpsSections = (clone $sections)->where('section_type', 'lkps');
        $responses = AccreditationResponse::query()->whereIn('accreditation_id', $accreditationIds);
        $totalResponses = (clone $responses)->count();
        $completedResponses = (clone $responses)->whereIn('status', ['submitted', 'verified', 'approved'])->count();
        $readinessItems = AccreditationReadinessItem::query()->whereIn('accreditation_id', $accreditationIds);
        $totalReadinessItems = (clone $readinessItems)->count();
        $completedReadinessItems = (clone $readinessItems)->whereIn('status', ['done', 'completed', 'verified'])->count();
        $mappingQuery = InstrumentMapping::query()->whereIn('instrument_version_id', (clone $accreditations)->pluck('instrument_version_id'));

        return [
            'spmi_evaluations' => $totalEvaluations,
            'spmi_met_rate' => $totalEvaluations === 0 ? 0.0 : round(($metEvaluations / $totalEvaluations) * 100, 2),
            'ami_open_findings' => AmiFinding::query()->whereHas('cycle', fn ($query) => $query->where('perguruan_tinggi_id', $perguruanTinggiId))->whereNotIn('status', ['closed'])->count(),
            'rtl_open' => RtlAction::query()->where('perguruan_tinggi_id', $perguruanTinggiId)->whereNotIn('status', ['closed'])->count(),
            'rtl_overdue' => RtlAction::query()->where('perguruan_tinggi_id', $perguruanTinggiId)->whereNotIn('status', ['closed'])->whereDate('due_date', '<', today())->count(),
            'accreditations' => $totalAccreditations,
            'accreditations_ready_rate' => $totalAccreditations === 0 ? 0.0 : round(($readyAccreditations / $totalAccreditations) * 100, 2),
            'led_progress' => $this->progress($ledSections->avg('readiness_percent')),
            'lkps_progress' => $this->progress($lkpsSections->avg('readiness_percent')),
            'response_completion_rate' => $totalResponses === 0 ? 0.0 : round(($completedResponses / $totalResponses) * 100, 2),
            'readiness_item_rate' => $totalReadinessItems === 0 ? 0.0 : round(($completedReadinessItems / $totalReadinessItems) * 100, 2),
            'sections' => $totalSections,
            'mapping_count' => $mappingQuery->count(),
        ];
    }

    private function progress(mixed $value): float
    {
        return round((float) ($value ?? 0), 2);
    }
}
