<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiFrameworks\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use App\Models\PerguruanTinggi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiFrameworkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Framework SPMI')
                ->description('Definisikan kerangka penjaminan mutu internal dan periode berlakunya.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship('perguruanTinggi', 'nama_pt', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('code')
                        ->label('Kode Framework')
                        ->helperText('Contoh: SPMI-2026 atau SPMI-PT-01.')
                        ->required()
                        ->alphaDash()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),
                    TextInput::make('name')
                        ->label('Nama Framework')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('version_label')
                        ->label('Label Versi')
                        ->placeholder('Contoh: Versi 1.0')
                        ->maxLength(100),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Draf',
                            'active' => 'Aktif',
                            'archived' => 'Diarsipkan',
                        ])
                        ->default('draft')
                        ->required(),
                    DatePicker::make('effective_from')
                        ->label('Mulai Berlaku'),
                    DatePicker::make('effective_until')
                        ->label('Berakhir')
                        ->after('effective_from'),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

