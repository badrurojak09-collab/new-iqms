<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiIndicators\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiIndicatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Indikator SPMI')
                ->description('Kelola data Indikator SPMI dalam satu formulir terstruktur.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                        Select::make('spmi_standard_id')->label('Standar SPMI')->relationship('standard', 'name', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forOptionalProgramStudi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))->searchable()->preload()->required(),
                        TextInput::make('code')->label('Kode Indikator')->required()->alphaDash()->maxLength(50)->unique(ignoreRecord: true),
                        TextInput::make('name')->label('Nama Indikator')->required()->maxLength(255),
                        Select::make('measurement_type')->label('Tipe Pengukuran')->options(['numeric' => 'Numerik', 'percentage' => 'Persentase', 'ratio' => 'Rasio', 'text' => 'Teks'])->required(),
                        TextInput::make('unit')->label('Satuan')->maxLength(50),
                        TextInput::make('weight')->label('Bobot')->numeric()->minValue(0),
                        Select::make('status')->label('Status')->options(['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'])->default('draft')->required(),
                        Textarea::make('validation_rules')->label('Aturan Validasi (JSON)')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}
