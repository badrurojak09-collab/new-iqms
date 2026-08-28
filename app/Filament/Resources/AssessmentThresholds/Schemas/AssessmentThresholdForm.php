<?php

namespace App\Filament\Resources\AssessmentThresholds\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentThresholdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ambang Batas Penilaian')
                ->description('Konfigurasikan aturan pembanding, arah evaluasi, agregasi, dan skor untuk versi instrumen tertentu.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    Select::make('assessment_element_id')->label('Elemen Penilaian')->relationship('element', 'title')->searchable()->preload(),
                    Select::make('assessment_indicator_id')->label('Indikator Penilaian')->relationship('indicator', 'name')->searchable()->preload(),
                    Select::make('assessment_scale_id')->label('Skala Penilaian')->relationship('scale', 'name')->searchable()->preload(),
                    Select::make('assessment_rubric_id')->label('Rubrik Penilaian')->relationship('rubric', 'label')->searchable()->preload(),
                    TextInput::make('code')->label('Kode Ambang Batas')->required()->maxLength(120)->alphaDash(),
                    TextInput::make('name')->label('Nama Ambang Batas')->required()->maxLength(255),
                    Select::make('comparison')->label('Operator Pembanding')->options(['gte' => 'Lebih Besar atau Sama Dengan', 'lte' => 'Lebih Kecil atau Sama Dengan', 'eq' => 'Sama Dengan', 'between' => 'Di Antara', 'target_match' => 'Sesuai Target'])->default('gte')->required(),
                    Select::make('direction')->label('Arah Evaluasi')->options(['auto' => 'Otomatis', 'higher_is_better' => 'Nilai Lebih Tinggi Lebih Baik', 'lower_is_better' => 'Nilai Lebih Rendah Lebih Baik', 'target_match' => 'Sesuai Target'])->default('auto')->required(),
                    TextInput::make('aggregation_key')->label('Kelompok Agregasi')->maxLength(120),
                    Select::make('aggregation_operator')->label('Operator Agregasi')->options(['all' => 'Semua Harus Lulus', 'any' => 'Salah Satu Boleh Lulus', 'weighted_average' => 'Rata-rata Berbobot', 'sum' => 'Jumlah'])->default('all')->required(),
                    TextInput::make('aggregation_min_passed')->label('Minimum Lulus')->numeric()->minValue(1),
                    TextInput::make('sequence')->label('Urutan Evaluasi')->numeric()->default(0)->required(),
                    TextInput::make('target_value')->label('Nilai Target')->numeric(),
                    TextInput::make('min_value')->label('Nilai Minimum')->numeric(),
                    TextInput::make('max_value')->label('Nilai Maksimum')->numeric(),
                    TextInput::make('pass_score')->label('Skor Lulus')->numeric()->default(100)->required(),
                    TextInput::make('fail_score')->label('Skor Gagal')->numeric()->default(0)->required(),
                    TextInput::make('minimum_score')->label('Skor Minimum')->numeric(),
                    TextInput::make('weight')->label('Bobot')->numeric()->default(1)->required(),
                    Select::make('status')->label('Status Konfigurasi')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku'])->default('draft')->disabled()->dehydrated(false),
                    Textarea::make('source_reference')->label('Referensi Sumber')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}
