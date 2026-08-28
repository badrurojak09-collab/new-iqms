<?php

namespace App\Filament\Resources\AssessmentScales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentScaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Skala Penilaian')
                ->description('Definisikan rentang dan tipe skala yang digunakan oleh versi instrumen tertentu.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Skala')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('name')->label('Nama Skala')->required()->maxLength(255),
                    Select::make('scale_type')->label('Jenis Skala')->options(['numeric' => 'Numerik', 'ordinal' => 'Ordinal', 'binary' => 'Biner', 'percentage' => 'Persentase', 'custom' => 'Kustom'])->required()->default('numeric'),
                    TextInput::make('min_value')->label('Nilai Minimum')->numeric()->required(),
                    TextInput::make('max_value')->label('Nilai Maksimum')->numeric()->required()->gte('min_value'),
                    TextInput::make('precision')->label('Jumlah Desimal')->numeric()->minValue(0)->maxValue(8)->default(2)->helperText('Jumlah angka di belakang koma.'),
                ]),
        ]);
    }
}
