<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('yayasan.nama')
                    ->label('Yayasan Default')
                    ->placeholder('Belum diatur')
                    ->searchable(),
                TextColumn::make('perguruanTinggi.nama_pt')
                    ->label('Perguruan Tinggi Default')
                    ->placeholder('Belum diatur')
                    ->searchable(),
                TextColumn::make('tenant_scopes_count')
                    ->label('Jumlah Scope')
                    ->counts('tenantScopes')
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Email Terverifikasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum terverifikasi')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (User $record): bool => ! $record->isSuperAdmin() && auth()->id() !== $record->getKey()),
            ])
            ->defaultSort('name');
    }
}
