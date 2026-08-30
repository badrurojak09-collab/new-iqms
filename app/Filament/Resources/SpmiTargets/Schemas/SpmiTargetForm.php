<?php declare(strict_types=1);

namespace App\Filament\Resources\SpmiTargets\Schemas;

use App\Support\Tenancy\TenantQuery;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SpmiTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Target SPMI')
                ->description('Kelola data Target SPMI dalam satu formulir terstruktur.')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('spmi_indicator_id')->label('Indikator SPMI')->relationship('indicator', 'name')->searchable()->preload()->required(),
                    Select::make('perguruan_tinggi_id')->label('Perguruan Tinggi')->relationship('perguruanTinggi', 'nama_pt')->searchable()->preload()->required(),
                    Select::make('program_studi_id')->label('Program Studi')->relationship('programStudi', 'nama_prodi')->searchable()->preload(),
                    TextInput::make('period_year')->label('Tahun Periode')->numeric()->minValue(2000)->maxValue(2100)->required(),
                    TextInput::make('period_code')->label('Kode Periode')->maxLength(50),
                    TextInput::make('target_numeric')->label('Target Numerik')->numeric(),
                    Textarea::make('target_text')->label('Target Teks')->rows(3),
                    Select::make('status')->label('Status')->options(['draft' => 'Draf', 'submitted' => 'Diajukan', 'approved' => 'Disetujui'])->default('draft')->required(),
                ]),
        ]);
    }
}
