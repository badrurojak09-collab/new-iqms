<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings;
use App\Support\Tenancy\TenantQuery;

use App\Filament\Resources\RtmMeetings\Pages\CreateRtmMeeting;
use App\Filament\Resources\RtmMeetings\Pages\EditRtmMeeting;
use App\Filament\Resources\RtmMeetings\Pages\ListRtmMeetings;
use App\Filament\Resources\RtmMeetings\RelationManagers\DecisionsRelationManager;
use App\Filament\Resources\RtmMeetings\RelationManagers\ParticipantsRelationManager;
use App\Filament\Resources\RtmMeetings\Schemas\RtmMeetingForm;
use App\Filament\Resources\RtmMeetings\Tables\RtmMeetingsTable;
use App\Models\RtmMeeting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RtmMeetingResource extends Resource
{
    protected static ?string $model = RtmMeeting::class;
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|UnitEnum|null $navigationGroup = 'AMI & Tindak Lanjut Mutu';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Rapat Tinjauan Manajemen';
    protected static ?string $modelLabel = 'Rapat Tinjauan Manajemen';
    protected static ?string $pluralModelLabel = 'Rapat Tinjauan Manajemen';

    public static function getEloquentQuery(): Builder
    {
        return TenantQuery::forOptionalProgramStudi(parent::getEloquentQuery(), auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return RtmMeetingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RtmMeetingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [ParticipantsRelationManager::class, DecisionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRtmMeetings::route('/'),
            'create' => CreateRtmMeeting::route('/create'),
            'edit' => EditRtmMeeting::route('/{record}/edit'),
        ];
    }
}
