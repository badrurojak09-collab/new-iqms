<?php

declare(strict_types=1);

namespace App\Filament\Resources\PerguruanTinggiStandards\Schemas;

use App\Support\Tenancy\TenantQuery;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

final class PerguruanTinggiStandardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identitas Standar Perguruan Tinggi')
                ->description('Standar PT menjadi payung bagi Standar SPMI, standar pelayanan, dan prosedur internal.')
                ->schema([
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship(
                            name: 'perguruanTinggi',
                            titleAttribute: 'nama_pt',
                            modifyQueryUsing: fn(Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user())
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('code')->label('Kode Standar PT')->required()->alphaDash()->maxLength(80)->unique(ignoreRecord: true),
                    TextInput::make('name')->label('Nama Standar')->required()->maxLength(255),
                    Select::make('category')->label('Kategori')->options(['academic' => 'Akademik', 'non_academic' => 'Non-Akademik', 'service' => 'Pelayanan Minimal', 'procedure' => 'SOP/Prosedur'])->default('academic')->required(),
                    Select::make('status')->label('Status')->options(['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'])->default('draft')->required(),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->minValue(0),
                    DatePicker::make('effective_from')->label('Mulai Berlaku'),
                    DatePicker::make('effective_until')->label('Berakhir'),
                    Textarea::make('statement')->label('Pernyataan Standar')->rows(4)->columnSpanFull(),
                    Textarea::make('basis')->label('Dasar/Referensi Standar')->rows(3)->columnSpanFull(),
                ])->columns(2)->columnSpanFull(),
        ]);
    }
}
