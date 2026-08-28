<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems;

use App\Filament\Resources\AmiChecklistItems\Pages\CreateAmiChecklistItem;
use App\Filament\Resources\AmiChecklistItems\Pages\EditAmiChecklistItem;
use App\Filament\Resources\AmiChecklistItems\Pages\ListAmiChecklistItems;
use App\Filament\Resources\AmiChecklistItems\Schemas\AmiChecklistItemForm;
use App\Filament\Resources\AmiChecklistItems\Tables\AmiChecklistItemsTable;
use App\Models\AmiChecklistItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AmiChecklistItemResource extends Resource
{
    protected static ?string $model = AmiChecklistItem::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'AMI & Tindak Lanjut Mutu';
    protected static ?int $navigationSort = 20;
    protected static ?string $navigationLabel = 'Checklist Audit';
    protected static ?string $modelLabel = 'Checklist Audit';
    protected static ?string $pluralModelLabel = 'Checklist Audit';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if (! $user || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereHas('cycle.perguruanTinggi', function (Builder $builder) use ($user): void {
            $builder->when($user->perguruan_tinggi_id, fn (Builder $q): Builder => $q->whereKey($user->perguruan_tinggi_id))
                ->when(! $user->perguruan_tinggi_id && $user->yayasan_id, fn (Builder $q): Builder => $q->where('yayasan_id', $user->yayasan_id))
                ->when(! $user->perguruan_tinggi_id && ! $user->yayasan_id, fn (Builder $q): Builder => $q->whereKey(0));
        });
    }

    public static function form(Schema $schema): Schema
    {
        return AmiChecklistItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AmiChecklistItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAmiChecklistItems::route('/'),
            'create' => CreateAmiChecklistItem::route('/create'),
            'edit' => EditAmiChecklistItem::route('/{record}/edit'),
        ];
    }
}
