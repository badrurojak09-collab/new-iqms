<?php

namespace App\Filament\Resources\AssessmentRubrics\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AssessmentRubricForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rubrik Penilaian')
                ->description('Tentukan label, rentang nilai, dan ekspektasi evidence untuk elemen instrumen.')
                ->icon('heroicon-o-list-bullet')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    Select::make('instrument_node_id')->label('Elemen Instrumen')->relationship('instrumentNode', 'title')->searchable()->preload(),
                    TextInput::make('label')->label('Label Rubrik')->required()->maxLength(255),
                    TextInput::make('min_score')->label('Nilai Minimum')->numeric()->required(),
                    TextInput::make('max_score')->label('Nilai Maksimum')->numeric()->required()->gte('min_score'),
                    Select::make('status')->label('Status Rubrik')->options(['draft' => 'Draf', 'review' => 'Dalam Review', 'approved' => 'Disetujui', 'retired' => 'Tidak Berlaku'])->default('draft')->disabled()->dehydrated(false),
                    Textarea::make('description')->label('Deskripsi Rubrik')->required()->rows(4)->columnSpanFull(),
                    Textarea::make('evidence_expectation')->label('Ekspektasi Evidence')->rows(4)->columnSpanFull(),
                    Textarea::make('approval_notes')->label('Catatan Persetujuan')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}
