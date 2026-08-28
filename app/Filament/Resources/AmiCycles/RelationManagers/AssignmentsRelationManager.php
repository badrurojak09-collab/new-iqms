<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\RelationManagers;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Penugasan AMI';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('assignment_role')->label('Peran')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'lead_auditor' => 'Auditor Utama',
                    'reviewer' => 'Reviewer',
                    default => 'Auditor',
                })->badge(),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'accepted' => 'Diterima',
                    'declined' => 'Ditolak',
                    default => 'Diundang',
                })->badge(),
                TextColumn::make('accepted_at')->label('Diterima Pada')->dateTime()->placeholder('—'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Penugasan')
                    ->visible(fn (): bool => auth()->user()?->can('manage ami') ?? false)
                    ->form([
                        Select::make('user_id')->label('Pengguna')->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload()->required(),
                        Select::make('assignment_role')->label('Peran')->options(['auditor' => 'Auditor', 'lead_auditor' => 'Auditor Utama', 'reviewer' => 'Reviewer'])->default('auditor')->required(),
                        Select::make('status')->label('Status Penugasan')->options(['invited' => 'Diundang', 'accepted' => 'Diterima', 'declined' => 'Ditolak'])->default('invited')->required(),
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Edit'),
                \Filament\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([]);
    }
}
