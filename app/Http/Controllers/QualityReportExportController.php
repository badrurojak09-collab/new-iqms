<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\DocumentOutput\Services\QualityReportService;
use App\Models\AmiCycle;
use App\Models\RtmMeeting;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Http\Response;

final class QualityReportExportController extends Controller
{
    public function __construct(
        private readonly QualityReportService $reportService
    ) {}

    public function exportRtmMinutes(RtmMeeting $meeting): Response
    {
        $user = auth()->user();
        if ($user && ! TenantQuery::canAccessTenantRecord($user, $meeting->perguruan_tinggi_id, $meeting->program_studi_id)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen RTM ini.');
        }

        $html = $this->reportService->exportRtmMinutesHtml($meeting);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    public function exportAmiSummary(AmiCycle $cycle): Response
    {
        $user = auth()->user();
        if ($user && ! TenantQuery::canAccessTenantRecord($user, $cycle->perguruan_tinggi_id, $cycle->program_studi_id)) {
            abort(403, 'Anda tidak memiliki akses ke dokumen AMI ini.');
        }

        $html = $this->reportService->exportAmiSummaryHtml($cycle);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }
}
