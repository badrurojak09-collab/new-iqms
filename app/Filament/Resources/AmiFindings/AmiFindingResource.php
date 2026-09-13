<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings;

use App\Filament\Clusters\AmiCluster;
use App\Support\Tenancy\TenantQuery;

use App\Filament\Resources\AmiFindings\Pages\CreateAmiFinding;
use App\Filament\Resources\AmiFindings\Pages\EditAmiFinding;
use App\Filament\Resources\AmiFindings\Pages\ListAmiFindings;
use App\Filament\Resources\AmiFindings\Schemas\AmiFindingForm;
use App\Filament\Resources\AmiFindings\Tables\AmiFindingsTable;
use App\Models\AmiFinding;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class AmiFindingResource extends Resource
{
    protected static ?string $model = AmiFinding::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderPlus;
    protected static ?string $cluster = AmiCluster::class;
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Temuan AMI';
    protected static ?string $modelLabel = 'Temuan AMI';
    protected static ?string $pluralModelLabel = 'Temuan AMI';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        if ($user?->isSuperAdmin()) {
            return $query;
        }
        return $query->whereHas('cycle', fn(Builder $related): Builder => TenantQuery::forOptionalProgramStudi($related, $user));
    }

    public static function form(Schema $schema): Schema
    {
        return AmiFindingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiFindingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AmiFindings\RelationManagers\EvidenceLinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmiFindings::route('/'),
            'create' => CreateAmiFinding::route('/create'),
            'edit' => EditAmiFinding::route('/{record}/edit'),
        ];
    }
}
