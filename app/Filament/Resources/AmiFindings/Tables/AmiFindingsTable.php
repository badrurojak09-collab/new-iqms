<?php declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AmiFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['cycle.perguruanTinggi']))
            ->columns([
                Stack::make([
                    Split::make([
                        // Kolom Kiri: Kode, Siklus, & Institusi
                        Stack::make([
                            TextColumn::make('code')
                                ->label('Kode Temuan')
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
                        ])->space(1),
                        // Kolom Kanan: Status & Badge
                        Stack::make([
                            TextColumn::make('classification')
                                ->label('Klasifikasi')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'nonconformity' => 'Ketidaksesuaian',
                                    'opportunity' => 'Peluang Perbaikan',
                                    default => 'Observasi',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'nonconformity' => 'danger',
                                    'opportunity' => 'info',
                                    default => 'warning',
                                }),
                            TextColumn::make('severity')
                                ->label('Keparahan')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'major' => 'Mayor',
                                    'minor' => 'Minor',
                                    'low' => 'Rendah',
                                    default => 'Sedang',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'major' => 'danger',
                                    'minor' => 'warning',
                                    'low' => 'gray',
                                    default => 'info',
                                }),
                            TextColumn::make('status')
                                ->label('Status')
                                ->formatStateUsing(fn(?string $state): string => match ($state) {
                                    'in_progress' => 'Dalam Tindak Lanjut',
                                    'closed' => 'Ditutup',
                                    default => 'Terbuka',
                                })
                                ->badge()
                                ->color(fn(?string $state): string => match ($state) {
                                    'closed' => 'success',
                                    'in_progress' => 'warning',
                                    default => 'danger',
                                }),
                            TextColumn::make('created_at')
                                ->label('Dibuat')
                                ->size('xs')
                                ->color('gray')
                                ->dateTime(),
                        ])->space(1),
                    ]),
                    // Teks Kondisi Utuh
                    TextColumn::make('condition')
                        ->label('Kondisi')
                        ->wrap()
                        ->extraAttributes(['class' => 'pt-2 font-medium text-gray-900 dark:text-gray-100']),
                ])->space(3),
            ])
            // contentGrid dihapus agar tidak menjadi double card
            ->filters([
                SelectFilter::make('classification')->label('Klasifikasi')->options(['observation' => 'Observasi', 'nonconformity' => 'Ketidaksesuaian', 'opportunity' => 'Peluang Perbaikan']),
                SelectFilter::make('severity')->label('Keparahan')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'minor' => 'Minor', 'major' => 'Mayor']),
                SelectFilter::make('status')->label('Status')->options(['open' => 'Terbuka', 'in_progress' => 'Dalam Tindak Lanjut', 'closed' => 'Ditutup']),
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
