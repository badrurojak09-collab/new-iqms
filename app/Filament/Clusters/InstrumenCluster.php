<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class InstrumenCluster extends Cluster
{


    protected static string|\BackedEnum|null $navigationIcon = Heroicon::DocumentCheck;
    protected static ?string $navigationLabel = 'Lembaga & Instrumen';
    protected static ?int $navigationSort = 1;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): ?string
    {
        return 'Instrument Registry';
    }
    public static function getClusterBreadcrumb(): string
    {
        return __('Instrumen');
    }
}
