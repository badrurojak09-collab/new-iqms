<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtlActions\Tables;

use App\Domain\Quality\RtlActionLifecycleService;
use App\Support\Ui\StatusLabel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RtlActionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode RTL')->searchable()->sortable()->copyable(),
                TextColumn::make('title')->label('Judul Tindak Lanjut')->searchable()->wrap(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->searchable()->sortable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->placeholder('Tingkat Perguruan Tinggi')->searchable(),
                TextColumn::make('owner.name')->label('Penanggung Jawab')->searchable(),
                TextColumn::make('readinessGap.item_key')->label('Gap Kesiapan')->placeholder('—')->searchable(),
                TextColumn::make('due_date')->label('Batas Waktu')->date()->sortable(),
                TextColumn::make('progress_percent')->label('Progress')->suffix('%')->sortable(),
                TextColumn::make('status')->label('Status RTL')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('verified_at')->label('Diverifikasi Pada')->dateTime()->placeholder('—')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status RTL')->options(['open' => 'Terbuka', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi', 'closed' => 'Ditutup', 'cancelled' => 'Dibatalkan']),
            ])
            ->recordActions([
                Action::make('start')->label('Mulai')->color('info')->visible(fn ($record): bool => auth()->user()?->can('manage rtl') && $record->status === 'open')->action(function ($record): void {
                    app(RtlActionLifecycleService::class)->transition($record, auth()->user(), 'in_progress');
                    Notification::make()->title('RTL dimulai.')->success()->send();
                }),
                Action::make('complete')->label('Selesaikan')->color('warning')->visible(fn ($record): bool => auth()->user()?->can('manage rtl') && $record->status === 'in_progress')->form([Textarea::make('reason')->label('Catatan Penyelesaian')])->action(function ($record, array $data): void {
                    app(RtlActionLifecycleService::class)->transition($record, auth()->user(), 'completed', $data['reason'] ?? null);
                    Notification::make()->title('RTL ditandai selesai.')->success()->send();
                }),
                Action::make('verify')->label('Verifikasi')->color('success')->visible(fn ($record): bool => auth()->user()?->can('verify rtl') && $record->status === 'completed')->requiresConfirmation()->action(function ($record): void {
                    app(RtlActionLifecycleService::class)->transition($record, auth()->user(), 'verified');
                    Notification::make()->title('RTL berhasil diverifikasi.')->success()->send();
                }),
                Action::make('close')->label('Tutup')->color('gray')->visible(fn ($record): bool => auth()->user()?->can('close rtl') && $record->status === 'verified')->requiresConfirmation()->action(function ($record): void {
                    app(RtlActionLifecycleService::class)->transition($record, auth()->user(), 'closed');
                    Notification::make()->title('RTL ditutup.')->success()->send();
                }),
                Action::make('cancel')->label('Batalkan')->color('danger')->visible(fn ($record): bool => auth()->user()?->can('manage rtl') && in_array($record->status, ['open', 'in_progress'], true))->form([Textarea::make('reason')->label('Alasan Pembatalan')->required()])->action(function ($record, array $data): void {
                    app(RtlActionLifecycleService::class)->transition($record, auth()->user(), 'cancelled', $data['reason']);
                    Notification::make()->title('RTL dibatalkan.')->success()->send();
                }),
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
