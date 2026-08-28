<?php

declare(strict_types=1);

namespace App\Filament\Resources\EvidenceCollections\Tables;

use App\Domain\Evidence\EvidenceCollectionApprovalService;
use App\Models\EvidenceCollection;
use App\Support\Ui\StatusLabel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EvidenceCollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode Koleksi')->searchable()->sortable()->copyable(),
                TextColumn::make('name')->label('Nama Koleksi')->searchable()->wrap(),
                TextColumn::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->sortable()->searchable(),
                TextColumn::make('programStudi.nama_prodi')->label('Program Studi')->sortable()->searchable()->placeholder('Tingkat Perguruan Tinggi'),
                TextColumn::make('provider')->label('Penyedia Cloud')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'google_drive' => 'Google Drive',
                    'sharepoint' => 'SharePoint/OneDrive',
                    'dropbox' => 'Dropbox',
                    default => 'Cloud Institusi',
                })->badge()->sortable(),
                TextColumn::make('status')->label('Status Koleksi')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('items_count')->counts('items')->label('Jumlah Persyaratan')->sortable(),
                TextColumn::make('root_folder_url')->label('Folder Utama')->url(fn (?string $state): ?string => $state)->limit(35)->openUrlInNewTab()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status Koleksi')->options(['draft' => 'Draf', 'active' => 'Aktif', 'approved' => 'Disetujui', 'locked' => 'Dikunci', 'archived' => 'Diarsipkan']),
                SelectFilter::make('provider')->label('Penyedia Cloud')->options(['google_drive' => 'Google Drive', 'sharepoint' => 'SharePoint/OneDrive', 'dropbox' => 'Dropbox', 'institution_cloud' => 'Cloud Institusi']),
                TrashedFilter::make()->label('Data Terhapus'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit')->visible(fn (EvidenceCollection $record): bool => $record->status !== 'locked'),
                Action::make('approve')->label('Setujui')->color('success')->requiresConfirmation()->visible(fn (EvidenceCollection $record): bool => in_array($record->status, ['draft', 'active'], true))->action(fn (EvidenceCollection $record): EvidenceCollection => app(EvidenceCollectionApprovalService::class)->approve(auth()->user(), $record)),
                Action::make('lock')->label('Kunci untuk Pengajuan')->color('warning')->requiresConfirmation()->visible(fn (EvidenceCollection $record): bool => $record->status === 'approved')->form([Textarea::make('reason')->label('Alasan Penguncian')->required()])->action(fn (EvidenceCollection $record, array $data): EvidenceCollection => app(EvidenceCollectionApprovalService::class)->lockForSubmission(auth()->user(), $record, $data['reason'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus'), ForceDeleteBulkAction::make()->label('Hapus Permanen'), RestoreBulkAction::make()->label('Pulihkan')]),
            ]);
    }
}
