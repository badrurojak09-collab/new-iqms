<?php

namespace App\Filament\Resources\AssessmentElements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentElementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Elemen Assessment')
                ->description('Definisikan elemen penilaian yang menjadi unit kerja akreditasi pada kriteria dan versi instrumen tertentu.')
                ->icon('heroicon-o-clipboard-document-check')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('assessment_criterion_id')->label('Kriteria Penilaian')->relationship('criterion', 'name')->searchable()->preload()->required(),
                    Select::make('instrument_node_id')->label('Elemen Instrumen')->relationship('instrumentNode', 'title')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Elemen Assessment')->required()->maxLength(120),
                    TextInput::make('title')->label('Judul Elemen Assessment')->required()->maxLength(500),
                    Select::make('element_type')->label('Jenis Elemen')->options(['qualitative' => 'Kualitatif', 'quantitative' => 'Kuantitatif', 'documentary' => 'Dokumenter', 'mixed' => 'Campuran'])->required()->default('mixed'),
                    TextInput::make('weight')->label('Bobot')->numeric()->minValue(0),
                    Toggle::make('is_required')->label('Wajib Dinilai')->default(false),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->required()->numeric()->default(0),
                    Textarea::make('metadata')->label('Metadata')->helperText('JSON metadata opsional.')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }
}
