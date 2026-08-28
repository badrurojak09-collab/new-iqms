<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiEvaluations\Schemas;

use App\Models\SpmiRealization;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiEvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Evaluasi SPMI')
                ->description('Kelola data Evaluasi SPMI dalam satu formulir terstruktur.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                        Select::make('spmi_realization_id')
                            ->label('Realisasi SPMI')
                            ->relationship('realization', 'id')
                            ->getOptionLabelFromRecordUsing(fn (SpmiRealization $record): string => self::realizationLabel($record))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih realisasi berdasarkan indikator, tahun, nilai, dan status. ID teknis tidak ditampilkan.'),
                        Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt')->searchable()->preload()->required(),
                        Select::make('program_studi_id')->label('Program Studi')->relationship('programStudi', 'nama_prodi')->searchable()->preload(),
                        Select::make('result')
                            ->label('Hasil Evaluasi')
                            ->options([
                                'met' => 'Tercapai (Met)',
                                'partially_met' => 'Tercapai Sebagian (Partially Met)',
                                'not_met' => 'Belum Tercapai (Not Met)',
                            ])
                            ->native(false)
                            ->required()
                            ->helperText('Tercapai: target terpenuhi. Tercapai Sebagian: ada kemajuan tetapi target belum sepenuhnya terpenuhi. Belum Tercapai: target belum terpenuhi.'),
                        TextInput::make('achievement_percentage')->label('Persentase Ketercapaian')->numeric()->minValue(0)->maxValue(100),
                        Textarea::make('analysis')->label('Analisis')->rows(4)->columnSpanFull(),
                        Textarea::make('root_cause')->label('Akar Masalah')->rows(4)->columnSpanFull(),
                        Textarea::make('recommendation')->label('Rekomendasi')->rows(4)->columnSpanFull(),
                        Select::make('status')->label('Status')->options(['draft' => 'Draf', 'submitted' => 'Diajukan', 'completed' => 'Selesai', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'])->default('draft')->required(),
                ]),
        ]);
    }

    private static function realizationLabel(SpmiRealization $record): string
    {
        $indicator = $record->indicator?->name ?? 'Indikator tidak tersedia';
        $value = $record->realization_numeric !== null
            ? rtrim(rtrim((string) $record->realization_numeric, '0'), '.')
            : ($record->realization_text ?: 'Tanpa nilai');
        $status = match ($record->status) {
            'verified' => 'Terverifikasi',
            'submitted' => 'Diajukan',
            'rejected' => 'Ditolak',
            default => 'Draf',
        };

        return sprintf('%s | Tahun %s | Nilai: %s | %s', $indicator, $record->period_year, $value, $status);
    }
}
