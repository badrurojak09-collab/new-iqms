<?php

namespace App\Filament\Resources\UserTenantScopes\Schemas;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\Yayasan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserTenantScopeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Pengaturan User Tenant Scope')
                ->description('Atur pengguna, peran, lingkup organisasi, dan masa berlaku akses.')
                ->icon('heroicon-o-identification')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('Pengguna')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('role_id')
                        ->label('Peran')
                        ->relationship('role', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('scope_type')
                        ->label('Tipe Scope')
                        ->options([
                            'yayasan' => 'Yayasan',
                            'perguruan_tinggi' => 'Perguruan Tinggi',
                            'program_studi' => 'Program Studi',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (callable $set): mixed => $set('scope_id', null)),
                    Select::make('scope_id')
                        ->label('Lingkup')
                        ->options(fn ($get): array => match ($get('scope_type')) {
                            'yayasan' => Yayasan::query()->orderBy('nama')->pluck('nama', 'id')->all(),
                            'perguruan_tinggi' => PerguruanTinggi::query()->orderBy('nama_pt')->pluck('nama_pt', 'id')->all(),
                            'program_studi' => ProgramStudi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->all(),
                            default => [],
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Toggle::make('is_default')
                        ->label('Scope Default')
                        ->helperText('Gunakan sebagai lingkup utama pengguna.'),
                    DatePicker::make('starts_at')
                        ->label('Mulai Berlaku'),
                    DatePicker::make('ends_at')
                        ->label('Berakhir')
                        ->after('starts_at'),
                ]),
        ]);
    }
}

