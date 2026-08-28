<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SpmiImprovementProgram;
use App\Support\Tenancy\TenantContext;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class VerifiedSpmiProgramsChart extends ChartWidget
{
    protected ?string $heading = 'Program Peningkatan SPMI Terverifikasi';

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $ptId = app(TenantContext::class)->perguruanTinggiId();
        $months = collect(range(5, 0))->map(fn (int $monthsAgo) => Carbon::now()->subMonths($monthsAgo)->startOfMonth());
        $data = $ptId === null ? $months->map(fn (): int => 0) : $months->map(function (Carbon $month) use ($ptId): int {
            return SpmiImprovementProgram::query()
                ->where('perguruan_tinggi_id', $ptId)
                ->where('status', 'verified')
                ->whereBetween('verified_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->count();
        });

        return [
            'datasets' => [[
                'label' => 'Verified',
                'data' => $data->all(),
                'borderColor' => '#7c3aed',
                'backgroundColor' => 'rgba(124, 58, 237, 0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $months->map(fn (Carbon $month): string => $month->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
