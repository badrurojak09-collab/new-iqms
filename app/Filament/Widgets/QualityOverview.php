<?php

namespace App\Filament\Widgets;

use App\Domain\Reporting\QualityDashboardMetrics;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;

class QualityOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $tenant = app(TenantContext::class);
        $ptId = $tenant->perguruanTinggiId();
        if ($ptId === null) {
            return [Stat::make('Tenant aktif', 'Belum dipilih')->description('Pilih scope Perguruan Tinggi untuk melihat metrik.')];
        }

        $metrics = app(QualityDashboardMetrics::class)->forPerguruanTinggi($ptId);

        return [
            Stat::make('SPMI met rate', $metrics['spmi_met_rate'] . '%')
                ->description($metrics['spmi_evaluations'] . ' evaluasi'),
            Stat::make('Temuan AMI terbuka', $metrics['ami_open_findings'])
                ->description('Perlu ditindaklanjuti'),
            Stat::make('RTL overdue', $metrics['rtl_overdue'])
                ->description($metrics['rtl_open'] . ' RTL terbuka'),
            Stat::make('Readiness akreditasi', $metrics['accreditations_ready_rate'] . '%')
                ->description($metrics['accreditations'] . ' aggregate'),
        ];
    }
}
