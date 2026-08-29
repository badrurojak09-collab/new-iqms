<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Reporting\QualityDashboardMetrics;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;

class AccreditationProgress extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $ptId = app(TenantContext::class)->perguruanTinggiId();
        if ($ptId === null) {
            return [Stat::make('Progress akreditasi', 'Tenant belum dipilih')->description('Pilih Perguruan Tinggi aktif.')];
        }
        $metrics = app(QualityDashboardMetrics::class)->forPerguruanTinggi($ptId);

        return [
            Stat::make('Progress LED', $metrics['led_progress'] . '%')
                ->description($metrics['sections'] . ' section akreditasi'),
            Stat::make('Progress LKPS', $metrics['lkps_progress'] . '%')
                ->description('Readiness section LKPS'),
            Stat::make('Response completion', $metrics['response_completion_rate'] . '%')
                ->description('Response submitted/verified'),
            Stat::make('Readiness item', $metrics['readiness_item_rate'] . '%')
                ->description($metrics['mapping_count'] . ' mapping instrumen'),
        ];
    }
}
