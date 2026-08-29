<?php

namespace App\Filament\Resources\ProgramStudis\Schemas;

use App\Support\Tenancy\TenantQuery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProgramStudiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Input Data Program Studi')
                ->description('Kelola Data Program Studi')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship(
                            name: 'perguruanTinggi',
                            titleAttribute: 'nama_pt',
                            modifyQueryUsing: fn(Builder $query): Builder => TenantQuery::forPerguruanTinggi(
                                $query,
                                auth()->user(),
                                'id'
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('nama_prodi')
                        ->label('Nama Program Studi')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('kode_prodi')
                        ->label('Kode Prodi')
                        ->helperText('Kode dibuat otomatis saat Program Studi disimpan.')
                        ->readOnly()
                        ->nullable()
                        ->alphaDash()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),
                    Select::make('jenjang')
                        ->options([
                            'D3' => 'D3',
                            'D4' => 'D4',
                            'S1' => 'S1',
                            'S2' => 'S2',
                            'S3' => 'S3',
                            'Profesi' => 'Profesi',
                        ])
                        ->required(),
                    Select::make('status')
                        ->options([
                            'active' => 'Aktif',
                            'inactive' => 'Tidak Aktif',
                        ])
                        ->default('active')
                        ->required(),
                ])
        ]);
    }
}
