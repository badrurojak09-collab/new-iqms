<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SpmiImprovementProgram;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PpeppQualityCharts extends ChartWidget
{
    protected ?string $heading = 'Status Siklus PPEPP';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $ptId = app(TenantContext::class)->perguruanTinggiId();
        $statuses = ['planned', 'in_progress', 'completed', 'verified'];
        $counts = $ptId === null ? collect() : SpmiImprovementProgram::query()
            ->where('perguruan_tinggi_id', $ptId)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'datasets' => [[
                'label' => 'Program peningkatan',
                'data' => collect($statuses)->map(fn (string $status): int => (int) ($counts[$status] ?? 0))->all(),
                'backgroundColor' => ['#94a3b8', '#f59e0b', '#3b82f6', '#16a34a'],
            ]],
            'labels' => ['Planned', 'In progress', 'Completed', 'Verified'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
