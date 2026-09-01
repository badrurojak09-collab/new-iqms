<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Reporting\QualityDashboardMetrics;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\Widget;

class QualityOverview extends Widget
{
    protected string $view = 'filament.widgets.quality-overview';

    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = app(TenantContext::class);
        $ptId = $tenant->perguruanTinggiId();

        if ($ptId === null) {
            return [
                'hasTenant' => false,
                'metrics' => null,
            ];
        }

        $metrics = app(QualityDashboardMetrics::class)->forPerguruanTinggi($ptId);

        return [
            'hasTenant' => true,
            'metrics' => $metrics,
        ];
    }
}
