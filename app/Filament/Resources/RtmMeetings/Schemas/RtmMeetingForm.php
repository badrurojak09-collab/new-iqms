<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use App\Models\AmiCycle;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RtmMeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rapat Tinjauan Manajemen')
                ->description('Catat rapat manajemen, sumber siklus AMI, peserta, dan keputusan tindak lanjut mutu.')
                ->icon('heroicon-o-users')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->options(fn (): array => TenantQuery::forPerguruanTinggi(PerguruanTinggi::query(), auth()->user())->orderBy('nama_pt')->pluck('nama_pt', 'id')->all())->searchable()->preload()->required(),
                    Select::make('program_studi_id')->label('Program Studi')->options(fn (): array => TenantQuery::forProgramStudi(ProgramStudi::query(), auth()->user())->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->all())->searchable()->preload()->helperText('Kosongkan jika rapat berlaku untuk tingkat perguruan tinggi.'),
                    Select::make('ami_cycle_id')->label('Siklus AMI Terkait')->options(fn (): array => TenantQuery::forOptionalProgramStudi(AmiCycle::query(), auth()->user())->latest('period_year')->get()->mapWithKeys(fn (AmiCycle $cycle): array => [$cycle->id => $cycle->code.' — '.$cycle->name.' — '.$cycle->period_year])->all())->searchable()->preload(),
                    Select::make('chair_id')->label('Pimpinan Rapat')->options(fn (): array => User::query()->whereHas('tenantScopes')->orderBy('name')->pluck('name', 'id')->all())->searchable()->preload(),
                    TextInput::make('code')->label('Kode Rapat')->required()->maxLength(80),
                    TextInput::make('title')->label('Judul Rapat')->required()->maxLength(255),
                    DateTimePicker::make('held_at')->label('Waktu Pelaksanaan')->native(false),
                    Select::make('status')->label('Status Rapat')->options(['planned' => 'Direncanakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'])->default('planned')->required(),
                    Textarea::make('minutes')->label('Notulen Rapat')->rows(8)->columnSpanFull()->helperText('Dokumentasikan pembahasan, keputusan, dan komitmen tindak lanjut.'),
                ]),
        ]);
    }
}
