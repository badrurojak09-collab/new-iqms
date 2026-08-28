<?php

declare(strict_types=1);

namespace App\Filament\Resources\AuditLogs;

use App\Models\AuditLog;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Security';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Audit Log';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('old_values')->disabled()->columnSpanFull(),
            Textarea::make('new_values')->disabled()->columnSpanFull(),
            Textarea::make('context')->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(),
            TextColumn::make('user.name')->label('Pengguna')->searchable()->sortable(),
            TextColumn::make('event')->badge()->searchable()->sortable(),
            TextColumn::make('auditable_type')->label('Object')->limit(30),
            TextColumn::make('auditable_id')->label('ID')->sortable(),
            TextColumn::make('ip_address')->label('IP'),
            TextColumn::make('route')->limit(35),
        ])->defaultSort('created_at', 'desc')->recordActions([])->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditLogs::route('/'), 'edit' => Pages\ViewAuditLog::route('/{record}')];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
