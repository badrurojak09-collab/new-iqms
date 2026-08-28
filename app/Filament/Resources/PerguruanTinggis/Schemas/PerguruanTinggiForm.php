<?php

namespace App\Filament\Resources\PerguruanTinggis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PerguruanTinggiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Input Data Perguruan Tinggi')
                ->description('Kelola Data Perguruan Tinggi')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('yayasan_id')
                        ->label('Yayasan')
                        ->relationship('yayasan', 'nama')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('nama_pt')
                        ->label('Nama Perguruan Tinggi')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('kode_pt')
                        ->label('Kode PT')
                        ->helperText('Kode dibuat otomatis saat Perguruan Tinggi disimpan.')
                        ->readOnly()
                        ->nullable()
                        ->alphaDash()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),
                    Select::make('jenis')
                        ->options([
                            'universitas' => 'Universitas',
                            'institut' => 'Institut',
                            'sekolah_tinggi' => 'Sekolah Tinggi',
                            'politeknik' => 'Politeknik',
                            'akademi' => 'Akademi'
                        ])
                        ->required(),
                    Select::make('status')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Tidak Aktif'
                        ])
                        ->default('active')
                        ->required(),
                ]),
        ]);
    }
}
