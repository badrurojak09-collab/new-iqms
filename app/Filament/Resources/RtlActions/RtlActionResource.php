<?php

namespace App\Filament\Resources\RtlActions;
use App\Support\Tenancy\TenantQuery;

use App\Filament\Resources\RtlActions\Pages\CreateRtlAction;
use App\Filament\Resources\RtlActions\Pages\EditRtlAction;
use App\Filament\Resources\RtlActions\Pages\ListRtlActions;
use App\Filament\Resources\RtlActions\RelationManagers\EffectivenessReviewsRelationManager;
use App\Filament\Resources\RtlActions\Schemas\RtlActionForm;
use App\Filament\Resources\RtlActions\Tables\RtlActionsTable;
use App\Models\RtlAction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RtlActionResource extends Resource
{
    protected static ?string $model = RtlAction::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'AMI & Tindak Lanjut Mutu';

    protected static ?int $navigationSort = 50;

    protected static ?string $navigationLabel = 'RTL Action';

    protected static ?string $modelLabel = 'RTL Action';

    protected static ?string $pluralModelLabel = 'RTL Action';

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forOptionalProgramStudi(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return RtlActionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RtlActionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EffectivenessReviewsRelationManager::class,
            \App\Filament\Resources\RtlActions\RelationManagers\EvidenceLinksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRtlActions::route('/'),
            'create' => CreateRtlAction::route('/create'),
            'edit' => EditRtlAction::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
