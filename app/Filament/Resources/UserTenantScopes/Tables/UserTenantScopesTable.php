<?php

namespace App\Filament\Resources\UserTenantScopes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\UserTenantScope;
use App\Http\Controllers\ImpersonationController;
use Filament\Actions\Action;

class UserTenantScopesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->sortable(),
                TextColumn::make('scope_type')->label('Tipe Scope')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'yayasan' => 'Yayasan',
                    'perguruan_tinggi' => 'Perguruan Tinggi',
                    'program_studi' => 'Program Studi',
                    default => (string) $state,
                })->badge()->sortable(),
                TextColumn::make('scope_id')->label('Lingkup')->formatStateUsing(fn ($state, UserTenantScope $record): string => $record->scopeLabel())->searchable(),
                TextColumn::make('role.name')->label('Peran')->searchable()->sortable(),
                TextColumn::make('is_default')->label('Default')->badge()->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak')->color(fn (mixed $state): string => (bool) $state ? 'success' : 'gray'),
                TextColumn::make('starts_at')->label('Mulai Berlaku')->date('d M Y')->sortable(),
                TextColumn::make('ends_at')->label('Berakhir')->date('d M Y')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
                Action::make('impersonate')
                    ->label('Lihat Sebagai Pengguna')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Mulai Sesi Impersonate')
                    ->modalDescription('Anda akan melihat aplikasi sebagai pengguna ini. Seluruh aktivitas akan tetap tercatat atas nama super_admin.')
                    ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false)
                    ->action(fn (UserTenantScope $record) => app(ImpersonationController::class)->start(request(), $record->user)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
