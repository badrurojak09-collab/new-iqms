<?php

namespace App\Filament\Resources\EvidenceCollections;

use App\Filament\Resources\EvidenceCollections\Pages\CreateEvidenceCollection;
use App\Filament\Resources\EvidenceCollections\Pages\EditEvidenceCollection;
use App\Filament\Resources\EvidenceCollections\Pages\ListEvidenceCollections;
use App\Filament\Resources\EvidenceCollections\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\EvidenceCollections\Schemas\EvidenceCollectionForm;
use App\Filament\Resources\EvidenceCollections\Tables\EvidenceCollectionsTable;
use App\Models\EvidenceCollection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EvidenceCollectionResource extends Resource
{
    protected static ?string $model = EvidenceCollection::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Evidence Center';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Evidence Collections';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['perguruanTinggi', 'programStudi'])->withCount('items');
        $user = auth()->user();
        if ($user === null || $user->isSuperAdmin()) {
            return $user === null ? $query->whereRaw('1 = 0') : $query;
        }

        return $query->where('perguruan_tinggi_id', $user->perguruan_tinggi_id ?? 0)
            ->when($user->programStudis()->exists(), fn (Builder $builder) => $builder->whereIn('program_studi_id', $user->programStudis()->pluck('program_studi.id')));
    }

    public static function form(Schema $schema): Schema
    {
        return EvidenceCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvidenceCollectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvidenceCollections::route('/'),
            'create' => CreateEvidenceCollection::route('/create'),
            'edit' => EditEvidenceCollection::route('/{record}/edit'),
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
