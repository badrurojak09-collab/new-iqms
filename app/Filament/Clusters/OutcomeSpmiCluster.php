<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class OutcomeSpmiCluster extends Cluster
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::ArchiveBoxXMark;
    protected static ?string $navigationLabel = 'Realisasi SPMI';
    protected static ?int $navigationSort = 2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): ?string
    {
        return 'SPMI';
    }
    public static function getClusterBreadcrumb(): string
    {
        return __('Realisasi SPMI');
    }
}
