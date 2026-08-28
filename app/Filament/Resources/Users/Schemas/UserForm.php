<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Models\PerguruanTinggi;
use App\Models\User;
use App\Models\Yayasan;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Pengguna')
                ->description('Kelola identitas akun, password, peran, dan lingkup default pengguna.')
                ->icon('heroicon-o-user')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Lengkap')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('Alamat Email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->confirmed()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->helperText('Minimal 8 karakter. Kosongkan saat edit jika password tidak ingin diubah.'),
                    TextInput::make('password_confirmation')
                        ->label('Konfirmasi Password')
                        ->password()
                        ->revealable()
                        ->dehydrated(false)
                        ->required(fn (string $operation): bool => $operation === 'create'),
                    Select::make('roles')
                        ->label('Peran Aplikasi')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->disabled(fn (?User $record): bool => $record?->isSuperAdmin() ?? false)
                        ->helperText('Peran super_admin pada akun super administrator tidak dapat dilepas dari form ini.'),
                    Select::make('yayasan_id')
                        ->label('Yayasan Default')
                        ->options(fn (): array => Yayasan::query()->orderBy('nama')->pluck('nama', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi Default')
                        ->options(fn (): array => PerguruanTinggi::query()->orderBy('nama_pt')->pluck('nama_pt', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('default_scope_type')
                        ->label('Tipe Scope Default')
                        ->options([
                            'yayasan' => 'Yayasan',
                            'perguruan_tinggi' => 'Perguruan Tinggi',
                            'program_studi' => 'Program Studi',
                        ])
                        ->nullable(),
                    TextInput::make('default_scope_id')
                        ->label('ID Scope Default')
                        ->numeric()
                        ->nullable()
                        ->helperText('Untuk pengelolaan scope lengkap, gunakan menu User Tenant Scope.'),
                    DateTimePicker::make('email_verified_at')
                        ->label('Email Terverifikasi Pada')
                        ->seconds(false)
                        ->nullable(),
                ]),
        ]);
    }
}
