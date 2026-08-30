<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiStandards\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiStandardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Standar SPMI')
                ->description('Kelola data Standar SPMI dalam satu formulir terstruktur.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                        Select::make('spmi_framework_id')->label('Framework SPMI')->relationship('framework', 'name', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))->searchable()->preload()->required(),
                        Select::make('program_studi_id')->label('Program Studi')->relationship('programStudi', 'nama_prodi', modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forProgramStudi($query, auth()->user()))->searchable()->preload(),
                        TextInput::make('code')->label('Kode Standar')->required()->alphaDash()->maxLength(50)->unique(ignoreRecord: true),
                        TextInput::make('name')->label('Nama Standar')->required()->maxLength(255),
                        Select::make('status')->label('Status')->options(['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'])->default('draft')->required(),
                        TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                        Textarea::make('statement')->label('Pernyataan Standar')->rows(4)->columnSpanFull(),
                        Textarea::make('basis')->label('Dasar Standar')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}
