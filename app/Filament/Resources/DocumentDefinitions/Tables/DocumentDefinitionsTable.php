<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentDefinitions\Tables;

use App\Domain\DocumentOutput\GenericReportService;
use App\Models\DocumentGenerationRequest;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class DocumentDefinitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Kode')->searchable()->sortable(),
            TextColumn::make('name')->label('Nama Dokumen')->searchable()->sortable(),
            TextColumn::make('domain')->label('Modul Sumber')->badge()->sortable(),
            TextColumn::make('scope_type')->label('Tingkat Scope')->placeholder('Semua scope')->sortable(),
            TextColumn::make('supported_formats')->label('Format')->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', array_map('strtoupper', $state)) : (string) $state),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            TextColumn::make('created_at')->label('Dibuat Pada')->dateTime()->sortable(),
        ])->filters([])
            ->recordActions([
                EditAction::make()->label('Edit'),
                Action::make('generate')
                    ->label('Buat Laporan Generik')
                    ->icon('heroicon-m-document-chart-bar')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $user = auth()->user();
                        abort_unless($user, 403);
                        $request = DocumentGenerationRequest::query()->create([
                            'document_definition_id' => $record->getKey(),
                            'requested_by' => $user->getKey(),
                            'period_label' => (string) now()->year,
                            'parameters' => ['source' => 'filament'],
                            'status' => 'queued',
                        ]);
                        app(GenericReportService::class)->generate($request);
                    })
                    ->successNotificationTitle('Laporan generik berhasil dibuat'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus')])]);
    }
}
