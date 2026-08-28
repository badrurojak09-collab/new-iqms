<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\Schemas;

use App\Models\InstrumentVersion;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiCycleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Siklus Audit Mutu Internal')
                ->description('Kelola periode, ruang lingkup, instrumen, dan koordinator pelaksanaan AMI.')
                ->icon('heroicon-o-clipboard-document-check')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->options(fn (): array => PerguruanTinggi::query()->orderBy('nama_pt')->pluck('nama_pt', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('program_studi_id')
                        ->label('Program Studi')
                        ->options(fn (): array => ProgramStudi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->helperText('Kosongkan jika audit berada pada tingkat perguruan tinggi.'),
                    Select::make('instrument_version_id')
                        ->label('Versi Instrumen')
                        ->options(fn (): array => InstrumentVersion::query()->with('family')->latest('id')->get()->mapWithKeys(fn (InstrumentVersion $version): array => [
                            $version->id => ($version->family?->name ?? 'Instrumen').' — '.$version->version_label,
                        ])->all())
                        ->searchable()
                        ->preload()
                        ->helperText('Opsional. Pilih instrumen yang digunakan untuk checklist audit.'),
                    Select::make('coordinator_id')
                        ->label('Koordinator AMI')
                        ->options(fn (): array => User::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->preload(),
                    TextInput::make('code')
                        ->label('Kode Siklus')
                        ->required()
                        ->maxLength(80)
                        ->helperText('Contoh: AMI-PT-2026 atau AMI-PRODI-2026.'),
                    TextInput::make('name')
                        ->label('Nama Siklus')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('period_year')
                        ->label('Tahun Periode')
                        ->numeric()
                        ->minValue(2000)
                        ->maxValue(2100)
                        ->required(),
                    Select::make('scope_type')
                        ->label('Ruang Lingkup')
                        ->options(['institution' => 'Perguruan Tinggi', 'program_study' => 'Program Studi'])
                        ->default('institution')
                        ->required(),
                    DatePicker::make('planned_start')->label('Rencana Mulai')->native(false),
                    DatePicker::make('planned_end')->label('Rencana Selesai')->native(false),
                    DatePicker::make('actual_start')->label('Mulai Aktual')->native(false)->disabled()->dehydrated(false),
                    DatePicker::make('actual_end')->label('Selesai Aktual')->native(false)->disabled()->dehydrated(false),
                    Select::make('status')
                        ->label('Status Siklus')
                        ->options(['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'completed' => 'Selesai', 'closed' => 'Ditutup'])
                        ->default('draft')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Status diubah melalui action lifecycle agar proses audit tetap terkendali.'),
                ]),
        ]);
    }
}
