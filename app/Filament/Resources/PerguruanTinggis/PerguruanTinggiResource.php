<?php

namespace App\Filament\Resources\PerguruanTinggis;

use App\Filament\Resources\PerguruanTinggis\Pages\CreatePerguruanTinggi;
use App\Filament\Resources\PerguruanTinggis\Pages\EditPerguruanTinggi;
use App\Filament\Resources\PerguruanTinggis\Pages\ListPerguruanTinggis;
use App\Filament\Resources\PerguruanTinggis\Schemas\PerguruanTinggiForm;
use App\Filament\Resources\PerguruanTinggis\Tables\PerguruanTinggisTable;
use App\Filament\Support\TenantAwareGlobalSearch;
use App\Models\PerguruanTinggi;
use App\Support\Tenancy\TenantQuery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;

class PerguruanTinggiResource extends Resource
{
    use TenantAwareGlobalSearch;

    protected static ?string $model = PerguruanTinggi::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Organisasi & Tenant';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Perguruan Tinggi';

    protected static ?string $modelLabel = 'Perguruan Tinggi';

    protected static ?string $pluralModelLabel = 'Perguruan Tinggi';

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forPerguruanTinggi(
            parent::getEloquentQuery(),
            auth()->user(),
            'id'  // Pakai 'id' karena ini Resource PerguruanTinggi itu sendiri
        );
    }

    public static function form(Schema $schema): Schema
    {
        return PerguruanTinggiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PerguruanTinggisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPerguruanTinggis::route('/'),
            'create' => CreatePerguruanTinggi::route('/create'),
            'edit' => EditPerguruanTinggi::route('/{record}/edit'),
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
