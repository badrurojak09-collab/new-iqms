<?php

namespace App\Filament\Resources\Yayasans;

use App\Filament\Resources\Yayasans\Pages\CreateYayasan;
use App\Filament\Resources\Yayasans\Pages\EditYayasan;
use App\Filament\Resources\Yayasans\Pages\ListYayasans;
use App\Filament\Resources\Yayasans\Schemas\YayasanForm;
use App\Filament\Resources\Yayasans\Tables\YayasansTable;
use App\Filament\Support\TenantAwareGlobalSearch;
use App\Models\Yayasan;
use App\Support\Tenancy\TenantQuery;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;

class YayasanResource extends Resource
{
    use TenantAwareGlobalSearch;

    protected static ?string $model = Yayasan::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Organisasi & Tenant';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Yayasan';

    protected static ?string $modelLabel = 'Yayasan';

    protected static ?string $pluralModelLabel = 'Yayasan';

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forYayasan(
            parent::getEloquentQuery(),
            auth()->user()
        );
    }

    public static function form(Schema $schema): Schema
    {
        return YayasanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return YayasansTable::configure($table);
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
            'index' => ListYayasans::route('/'),
            'create' => CreateYayasan::route('/create'),
            'edit' => EditYayasan::route('/{record}/edit'),
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
