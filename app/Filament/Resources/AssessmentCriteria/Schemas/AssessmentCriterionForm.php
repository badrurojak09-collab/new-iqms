<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssessmentCriteria\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentCriterionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kriteria Kanonik')
                ->description('Definisikan kriteria penilaian yang menjadi acuan pada versi instrumen tertentu.')
                ->icon('heroicon-o-book-open')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    Select::make('instrument_node_id')->label('Elemen Instrumen')->relationship('instrumentNode', 'title')->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Kriteria')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('name')->label('Nama Kriteria')->required()->maxLength(500),
                    TextInput::make('weight')->label('Bobot (%)')->numeric()->minValue(0)->maxValue(100),
                    TextInput::make('minimum_score')->label('Skor Minimum')->numeric()->minValue(0),
                    TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->default(0)->minValue(0)->required(),
                    Select::make('is_required')->label('Kriteria Wajib')->options([1 => 'Wajib', 0 => 'Tidak Wajib'])->default(1)->required(),
                ]),
        ]);
    }
}
