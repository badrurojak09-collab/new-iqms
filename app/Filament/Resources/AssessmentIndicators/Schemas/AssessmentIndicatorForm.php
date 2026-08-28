<?php

namespace App\Filament\Resources\AssessmentIndicators\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentIndicatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Indikator Penilaian')
                ->description('Definisikan indikator, satuan, arah evaluasi, dan tipe data yang digunakan dalam assessment.')
                ->icon('heroicon-o-chart-bar')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('assessment_element_id')->label('Elemen Penilaian')->relationship('element', 'title')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Indikator')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('name')->label('Nama Indikator')->required()->maxLength(500),
                    TextInput::make('unit')->label('Satuan')->maxLength(100),
                    Select::make('direction')->label('Arah Evaluasi')->options(['higher_is_better' => 'Nilai Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Nilai Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target'])->required()->default('higher_is_better'),
                    Select::make('data_type')->label('Tipe Data')->options(['integer' => 'Bilangan Bulat', 'decimal' => 'Desimal', 'percentage' => 'Persentase', 'currency' => 'Mata Uang', 'text' => 'Teks', 'boolean' => 'Ya/Tidak', 'date' => 'Tanggal'])->required()->default('decimal'),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->default(0)->required(),
                    Select::make('is_required')->label('Indikator Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib'])->default(0)->required(),
                ]),
        ]);
    }
}
