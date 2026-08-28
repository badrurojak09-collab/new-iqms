<?php

namespace App\Filament\Resources\Yayasans\Schemas;

use App\Filament\Resources\Yayasans\YayasanResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class YayasanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Input Data Yayasan')
                ->description('Kelola Data Yayasan')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('nama')
                        ->label('Nama Yayasan')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('kode')
                        ->label('Kode Yayasan')
                        ->helperText('Kode dibuat otomatis saat Yayasan disimpan.')
                        ->readOnly()
                        ->nullable()
                        ->alphaDash()
                        ->maxLength(50)
                        ->hidden()
                        ->unique(ignoreRecord: true),

                    // TOMBOL ACTION DI DALAM SECTION
                    Actions::make([
                        // 1. Tombol "Buat" (HANYA MUNCUL DI HALAMAN CREATE)
                        Action::make('create')
                            ->label('Buat')
                            ->submit('form')
                            ->color('primary')
                            ->visible(fn(string $operation): bool => $operation === 'create'),

                        // 2. Tombol "Buat & Buat Lainnya" (HANYA MUNCUL DI HALAMAN CREATE)
                        Action::make('createAnother')
                            ->label('Buat & buat lainnya')
                            ->action('createAnother')
                            ->color('gray')
                            ->visible(fn(string $operation): bool => $operation === 'create'),

                        // 3. Tombol "Simpan Perubahan" (HANYA MUNCUL DI HALAMAN EDIT)
                        Action::make('save')
                            ->label('Simpan perubahan')
                            ->submit('form')
                            ->color('primary')
                            ->visible(fn(string $operation): bool => $operation === 'edit'),

                        // 4. Tombol "Batal" (MUNCUL DI HALAMAN CREATE & EDIT)
                        Action::make('cancel')
                            ->label('Batal')
                            ->url(fn(): string => YayasanResource::getUrl('index'))
                            ->color('gray'),
                    ])
                        ->columnSpanFull()
                        ->alignLeft(),
                ]),
        ]);
    }
}
