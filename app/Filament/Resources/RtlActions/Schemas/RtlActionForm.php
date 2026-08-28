<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtlActions\Schemas;

use App\Models\ReadinessGap;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RtlActionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rencana Tindak Lanjut Mutu')
                ->description('Kelola tindakan perbaikan, penanggung jawab, target penyelesaian, evidence, dan proses verifikasi RTL.')
                ->icon('heroicon-o-clipboard-document-check')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
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
                        ->helperText('Kosongkan jika RTL berlaku pada tingkat perguruan tinggi.'),
                    Select::make('readiness_gap_id')
                        ->label('Gap Kesiapan')
                        ->relationship('readinessGap', 'description')
                        ->getOptionLabelFromRecordUsing(fn (ReadinessGap $record): string => sprintf('%s — %s', $record->item_key ?: 'Gap', $record->description ?: 'Tanpa deskripsi'))
                        ->searchable()
                        ->preload(),
                    Select::make('owner_id')
                        ->label('Penanggung Jawab')
                        ->relationship('owner', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('code')->label('Kode RTL')->required()->maxLength(80),
                    TextInput::make('title')->label('Judul Tindak Lanjut')->required()->maxLength(255),
                    DatePicker::make('due_date')->label('Batas Waktu')->native(false),
                    TextInput::make('progress_percent')->label('Progress (%)')->required()->numeric()->minValue(0)->maxValue(100)->default(0)->suffix('%'),
                    Select::make('status')
                        ->label('Status RTL')
                        ->options(['open' => 'Terbuka', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi', 'closed' => 'Ditutup', 'cancelled' => 'Dibatalkan'])
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Status berubah melalui lifecycle RTL dan proses verifikasi.'),
                    Textarea::make('action_plan')->label('Rencana Tindakan')->required()->rows(5)->columnSpanFull()->helperText('Jelaskan tindakan, keluaran, dan ukuran keberhasilannya.'),
                    Textarea::make('evidence_of_completion')->label('Bukti/Catatan Penyelesaian')->rows(4)->columnSpanFull(),
                    TextInput::make('verified_by')->label('Diverifikasi Oleh')->disabled()->dehydrated(false),
                ]),
        ]);
    }
}
