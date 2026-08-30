<?php declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AmiChecklistItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['cycle.perguruanTinggi', 'cycle.programStudi']))
            ->columns([
                Stack::make([
                    Split::make([
                        // Kolom Kiri: Kode & Info Institusi
                        Stack::make([
                            TextColumn::make('code')
                                ->label('Kode')
                                ->weight('bold')
                                ->searchable()
                                ->sortable(),
                            TextColumn::make('cycle.code')
                                ->label('Siklus AMI')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('cycle.perguruanTinggi.nama_pt')
                                ->label('Perguruan Tinggi')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                            TextColumn::make('cycle.programStudi.nama_prodi')
                                ->label('Program Studi')
                                ->placeholder('Tingkat Perguruan Tinggi')
                                ->color('gray')
                                ->size('sm')
                                ->searchable(),
                        ])->space(1),
                        // Kolom Kanan: Status & Badge
                        Stack::make([
                            TextColumn::make('response_status')
                                ->label('Status Respons')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'completed' => 'Selesai',
                                    'verified' => 'Terverifikasi',
                                    default => 'Belum Dimulai',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'completed', 'verified' => 'success',
                                    'in_progress' => 'warning',
                                    default => 'gray',
                                }),
                            TextColumn::make('response_type')
                                ->label('Jenis Respons')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'boolean' => 'Ya/Tidak',
                                    'numeric' => 'Numerik',
                                    'choice' => 'Pilihan',
                                    default => 'Teks',
                                })
                                ->badge()
                                ->color('info'),
                            TextColumn::make('evidence_required')
                                ->label('Evidence')
                                ->formatStateUsing(fn($state): string => $state ? 'Wajib Evidence' : 'Tidak Wajib Evidence')
                                ->badge()
                                ->color(fn($state): string => $state ? 'danger' : 'gray'),
                        ])->space(1),
                    ]),
                    // Teks Pertanyaan Audit Utuh
                    TextColumn::make('question')
                        ->label('Pertanyaan Audit')
                        ->wrap()
                        ->extraAttributes(['class' => 'pt-2 font-medium text-gray-900 dark:text-gray-100'])
                        ->searchable(),
                ])->space(3),
            ])
            // contentGrid dihapus agar tidak menjadi double card
            ->filters([
                SelectFilter::make('response_status')->label('Status Respons')->options(['not_started' => 'Belum Dimulai', 'in_progress' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi']),
                SelectFilter::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'boolean' => 'Ya/Tidak', 'numeric' => 'Numerik', 'choice' => 'Pilihan']),
                SelectFilter::make('evidence_required')->label('Evidence')->options([1 => 'Wajib', 0 => 'Tidak Wajib']),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Hapus yang dipilih')]),
            ]);
    }
}
