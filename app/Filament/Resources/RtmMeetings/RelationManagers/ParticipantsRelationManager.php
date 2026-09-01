<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\RelationManagers;

use Illuminate\Database\Eloquent\Builder;
use App\Support\Tenancy\TenantQuery;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Peserta Rapat';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nama Peserta')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('role')->label('Peran')->placeholder('—'),
                TextColumn::make('attended')->label('Kehadiran')->formatStateUsing(fn ($state): string => $state ? 'Hadir' : 'Tidak Hadir')->badge(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()->label('Tambah Peserta')->visible(fn (): bool => auth()->user()?->can('manage rtm') ?? false)->form([
                    Select::make('user_id')->label('Pengguna')->options(fn (): array => User::query()->whereHas('tenantScopes')->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->required(),
                    TextInput::make('role')->label('Peran dalam Rapat')->maxLength(50),
                    Toggle::make('attended')->label('Hadir')->default(false),
                ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Edit'),
                \Filament\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([]);
    }
}
