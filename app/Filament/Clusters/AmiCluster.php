<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class AmiCluster extends Cluster
{


    protected static string|\BackedEnum|null $navigationIcon = Heroicon::AdjustmentsVertical;
    protected static ?string $navigationLabel = 'AMI';
    protected static ?int $navigationSort = 1;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationGroup(): ?string
    {
        return 'AMI & Tindak Lanjut Mutu';
    }
    public static function getClusterBreadcrumb(): string
    {
        return __('AMI');
    }
}
