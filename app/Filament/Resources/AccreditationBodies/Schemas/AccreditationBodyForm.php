<?php

namespace App\Filament\Resources\AccreditationBodies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccreditationBodyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lembaga Akreditasi')
                ->description('Kelola lembaga akreditasi seperti BAN-PT atau LAM yang menjadi sumber instrumen.')
                ->icon('heroicon-o-building-library')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('code')->label('Kode Lembaga')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('name')->label('Nama Lembaga')->required()->maxLength(255),
                    Select::make('kind')->label('Jenis Lembaga')->options([
                        'national' => 'Nasional',
                        'lam' => 'Lembaga Akreditasi Mandiri (LAM)',
                        'external' => 'Eksternal Lainnya',
                    ])->required()->default('external'),
                    Select::make('status')->label('Status')->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ])->required()->default('active'),
                    TextInput::make('website')->label('Situs Web')->url()->maxLength(255)->columnSpanFull(),
                ]),
        ]);
    }
}
