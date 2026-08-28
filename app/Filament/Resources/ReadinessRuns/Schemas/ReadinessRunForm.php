<?php

namespace App\Filament\Resources\ReadinessRuns\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReadinessRunForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Ringkasan Readiness')
                ->description('Hasil perhitungan kesiapan bersifat read-only dan ditelusuri berdasarkan kegiatan akreditasi serta versi instrumen.')
                ->icon('heroicon-o-chart-bar')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Placeholder::make('accreditation.title')->label('Kegiatan Akreditasi'),
                    Placeholder::make('instrumentVersion.version_label')->label('Versi Instrumen'),
                    Placeholder::make('status')->label('Status Perhitungan'),
                    Placeholder::make('total_items')->label('Total Item'),
                    Placeholder::make('ready_items')->label('Item Siap'),
                    Placeholder::make('completion_percent')->label('Persentase Penyelesaian'),
                    Placeholder::make('weighted_score')->label('Skor Berbobot'),
                    Placeholder::make('input_hash')->label('Hash Input'),
                    Placeholder::make('completed_at')->label('Selesai Pada'),
                ]),
        ]);
    }
}
