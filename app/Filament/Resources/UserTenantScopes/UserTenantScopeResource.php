<?php

namespace App\Filament\Resources\UserTenantScopes;

use App\Filament\Resources\UserTenantScopes\Pages\CreateUserTenantScope;
use App\Filament\Resources\UserTenantScopes\Pages\EditUserTenantScope;
use App\Filament\Resources\UserTenantScopes\Pages\ListUserTenantScopes;
use App\Filament\Resources\UserTenantScopes\Schemas\UserTenantScopeForm;
use App\Filament\Resources\UserTenantScopes\Tables\UserTenantScopesTable;
use App\Models\UserTenantScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserTenantScopeResource extends Resource
{
    protected static ?string $model = UserTenantScope::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Organisasi & Tenant';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Ruang Lingkup Pengguna';

    protected static ?string $modelLabel = 'Ruang Lingkup Pengguna';

    protected static ?string $pluralModelLabel = 'Ruang Lingkup Pengguna';

    public static function form(Schema $schema): Schema
    {
        return UserTenantScopeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTenantScopesTable::configure($table);
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
            'index' => ListUserTenantScopes::route('/'),
            'create' => CreateUserTenantScope::route('/create'),
            'edit' => EditUserTenantScope::route('/{record}/edit'),
        ];
    }
}
