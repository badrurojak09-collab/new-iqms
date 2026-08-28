<?php

declare(strict_types=1);

namespace App\Filament\Resources\SpmiImprovementPrograms\Schemas;

use App\Models\Accreditation;
use App\Models\SpmiEvaluation;
use App\Models\SpmiIndicator;
use App\Models\SpmiTarget;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiImprovementProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Program Peningkatan SPMI')
                ->description('Rencanakan, pantau, dan verifikasi program peningkatan mutu berdasarkan hasil evaluasi SPMI.')
                ->icon('heroicon-o-arrow-trending-up')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('spmi_evaluation_id')
                        ->label('Evaluasi SPMI')
                        ->relationship('evaluation', 'id')
                        ->getOptionLabelFromRecordUsing(fn (SpmiEvaluation $record): string => self::evaluationLabel($record))
                        ->searchable()
                        ->preload()
                        ->helperText('Opsional. Pilih evaluasi yang menjadi dasar program peningkatan.'),
                    Select::make('spmi_indicator_id')
                        ->label('Indikator SPMI')
                        ->options(fn (): array => SpmiIndicator::query()
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn (SpmiIndicator $item): array => [$item->id => $item->code.' — '.$item->name])
                            ->all())
                        ->searchable()
                        ->preload(),
                    Select::make('spmi_target_id')
                        ->label('Target SPMI')
                        ->options(fn (): array => SpmiTarget::query()
                            ->with('indicator')
                            ->latest('period_year')
                            ->get()
                            ->mapWithKeys(fn (SpmiTarget $item): array => [
                                $item->id => ($item->indicator?->code ?? 'Indikator').' — Tahun '.$item->period_year.' — '.($item->target_numeric ?? $item->target_text ?? 'Belum diisi'),
                            ])
                            ->all())
                        ->searchable()
                        ->preload(),
                    Select::make('accreditation_id')
                        ->label('Kegiatan Akreditasi')
                        ->relationship('accreditation', 'title')
                        ->getOptionLabelFromRecordUsing(fn (Accreditation $record): string => self::accreditationLabel($record))
                        ->searchable()
                        ->preload()
                        ->helperText('Opsional. Hubungkan program dengan kegiatan akreditasi terkait.'),
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship('perguruanTinggi', 'nama_pt')
                        ->required()
                        ->searchable()
                        ->preload(),
                    Select::make('program_studi_id')
                        ->label('Program Studi')
                        ->relationship('programStudi', 'nama_prodi')
                        ->searchable()
                        ->preload()
                        ->helperText('Kosongkan jika program berlaku pada tingkat perguruan tinggi.'),
                    Select::make('owner_id')
                        ->label('Penanggung Jawab')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('code')
                        ->label('Kode Program')
                        ->required()
                        ->maxLength(80)
                        ->helperText('Gunakan kode unik untuk memudahkan pelacakan program.'),
                    TextInput::make('title')
                        ->label('Judul Program')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('action_plan')
                        ->label('Rencana Tindakan')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull()
                        ->helperText('Jelaskan tindakan, keluaran, dan ukuran keberhasilan yang akan dilakukan.'),
                    DatePicker::make('due_date')
                        ->label('Batas Waktu')
                        ->native(false),
                    TextInput::make('progress_percent')
                        ->label('Progress (%)')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%'),
                    Select::make('status')
                        ->label('Status Program')
                        ->options([
                            'planned' => 'Direncanakan',
                            'in_progress' => 'Sedang Berjalan',
                            'completed' => 'Selesai',
                            'verified' => 'Terverifikasi',
                        ])
                        ->default('planned')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Status berubah melalui lifecycle program dan proses verifikasi.'),
                    Textarea::make('completion_notes')
                        ->label('Catatan Penyelesaian')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Isi setelah tindakan selesai untuk mendokumentasikan hasil dan catatan verifikasi.'),
                ]),
        ]);
    }

    private static function evaluationLabel(SpmiEvaluation $record): string
    {
        $realization = $record->realization;
        $indicator = $realization?->indicator?->name ?? 'Realisasi tanpa indikator';
        $year = $realization?->period_year ?? '—';
        $result = match ($record->result) {
            'met' => 'Tercapai',
            'partially_met' => 'Tercapai Sebagian',
            'not_met' => 'Belum Tercapai',
            default => $record->result ?: 'Belum ditentukan',
        };

        return sprintf('%s | Tahun %s | Hasil: %s', $indicator, $year, $result);
    }

    private static function accreditationLabel(Accreditation $record): string
    {
        return sprintf('%s%s', $record->code ?: 'Akreditasi', $record->title ? ' — '.$record->title : '');
    }
}
