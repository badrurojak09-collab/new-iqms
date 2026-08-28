<?php

namespace App\Filament\Resources\UserTenantScopes;

use App\Filament\Resources\UserTenantScopes\Pages\CreateUserTenantScope;
use App\Filament\Resources\UserTenantScopes\Pages\EditUserTenantScope;
use App\Filament\Resources\UserTenantScopes\Pages\ListUserTenantScopes;
use App\Filament\Resources\UserTenantScopes\Schemas\UserTenantScopeForm;
use App\Filament\Resources\UserTenantScopes\Tables\UserTenantScopesTable;
use App\Models\UserTenantScope;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;

class UserTenantScopeResource extends Resource
{
    protected static ?string $model = UserTenantScope::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Lingkup Akses Pengguna';

    protected static ?string $modelLabel = 'Lingkup Akses Pengguna';

    protected static ?string $pluralModelLabel = 'Lingkup Akses Pengguna';

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
