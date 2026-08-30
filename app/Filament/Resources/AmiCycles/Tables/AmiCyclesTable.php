<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\Tables;

use App\Domain\Ami\AmiCycleLifecycleService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AmiCyclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['perguruanTinggi', 'programStudi']))
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Siklus')->searchable()->sortable(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('Tingkat Perguruan Tinggi')->searchable(),
                TextColumn::make('period_year')->label('Tahun')->sortable(),
                TextColumn::make('scope_type')->label('Ruang Lingkup')->formatStateUsing(fn (?string $state): string => $state === 'program_study' ? 'Program Studi' : 'Perguruan Tinggi')->badge(),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'in_progress' => 'Sedang Berjalan',
                    'completed' => 'Selesai',
                    'closed' => 'Ditutup',
                    default => 'Draf',
                })->badge()->color(fn (?string $state): string => match ($state) {
                    'in_progress' => 'warning',
                    'completed' => 'success',
                    'closed' => 'gray',
                    default => 'info',
                }),
            ])
            ->recordActions([
                Action::make('start')->label('Mulai Audit')->color('warning')->requiresConfirmation()->visible(fn ($record): bool => $record->status === 'draft' && auth()->user()?->can('manage ami'))->action(function ($record): void {
                    app(AmiCycleLifecycleService::class)->start($record, auth()->user());
                    Notification::make()->title('Siklus AMI dimulai.')->success()->send();
                }),
                Action::make('complete')->label('Tandai Selesai')->color('success')->requiresConfirmation()->visible(fn ($record): bool => $record->status === 'in_progress' && auth()->user()?->can('review ami'))->action(function ($record): void {
                    app(AmiCycleLifecycleService::class)->complete($record, auth()->user());
                    Notification::make()->title('Siklus AMI ditandai selesai.')->success()->send();
                }),
                Action::make('close')->label('Tutup Siklus')->color('gray')->requiresConfirmation()->visible(fn ($record): bool => $record->status === 'completed' && auth()->user()?->can('manage ami'))->action(function ($record): void {
                    app(AmiCycleLifecycleService::class)->close($record, auth()->user());
                    Notification::make()->title('Siklus AMI ditutup.')->success()->send();
                }),
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
