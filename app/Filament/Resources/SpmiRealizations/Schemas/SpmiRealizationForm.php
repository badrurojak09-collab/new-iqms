<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiRealizations\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiRealizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Realisasi SPMI')
                ->description('Kelola data Realisasi SPMI dalam satu formulir terstruktur.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                        Select::make('spmi_target_id')->label('Target SPMI')->relationship('target', 'period_code', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forOptionalProgramStudi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('spmi_indicator_id')->label('Indikator SPMI')->relationship('indicator', 'name', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forOptionalProgramStudi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('program_studi_id')->label('Program Studi')->relationship('programStudi', 'nama_prodi', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forProgramStudi($query, auth()->user()))->searchable()->preload(),
                        TextInput::make('period_year')->label('Tahun Periode')->numeric()->required(),
                        TextInput::make('realization_numeric')->label('Realisasi Numerik')->numeric(),
                        Textarea::make('realization_text')->label('Realisasi Teks')->rows(3),
                        Select::make('source_type')->label('Jenis Sumber')->options(['internal' => 'Internal', 'external' => 'Eksternal', 'system' => 'Sistem'])->required(),
                        TextInput::make('source_reference')->label('Referensi Sumber')->maxLength(255),
                        Select::make('status')->label('Status')->options(['draft' => 'Draf', 'submitted' => 'Diajukan', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'])->default('draft')->required(),
                ]),
        ]);
    }
}
