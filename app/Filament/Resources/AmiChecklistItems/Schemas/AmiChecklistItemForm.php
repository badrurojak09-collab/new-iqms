<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiChecklistItems\Schemas;
use App\Support\Tenancy\TenantQuery;
use Illuminate\Database\Eloquent\Builder;

use App\Models\AmiCycle;
use App\Models\InstrumentNode;
use App\Models\SpmiIndicator;
use App\Models\SpmiStandard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiChecklistItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Checklist Audit AMI')
                ->description('Kelola pertanyaan audit, pemetaan sumber mutu, respons auditor, dan status penyelesaiannya.')
                ->icon('heroicon-o-list-bullet')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('ami_cycle_id')->label('Siklus AMI')->options(fn (): array => TenantQuery::forOptionalProgramStudi(AmiCycle::query(), auth()->user())->latest('period_year')->get()->mapWithKeys(fn (AmiCycle $cycle): array => [$cycle->id => $cycle->code.' — '.$cycle->name.' — '.$cycle->period_year])->all())->searchable()->preload()->required(),
                    TextInput::make('code')->label('Kode Checklist')->required()->maxLength(100),
                    Textarea::make('question')->label('Pertanyaan Audit')->required()->rows(5)->columnSpanFull(),
                    Select::make('instrument_node_id')->label('Elemen Instrumen')->options(fn (): array => InstrumentNode::query()->orderBy('code')->get()->mapWithKeys(fn (InstrumentNode $node): array => [$node->id => $node->code.' — '.$node->title])->all())->searchable()->preload(),
                    Select::make('spmi_standard_id')->label('Standar SPMI')->options(fn (): array => SpmiStandard::query()->orderBy('code')->get()->mapWithKeys(fn (SpmiStandard $standard): array => [$standard->id => $standard->code.' — '.$standard->name])->all())->searchable()->preload(),
                    Select::make('spmi_indicator_id')->label('Indikator SPMI')->options(fn (): array => SpmiIndicator::query()->orderBy('code')->get()->mapWithKeys(fn (SpmiIndicator $indicator): array => [$indicator->id => $indicator->code.' — '.$indicator->name])->all())->searchable()->preload(),
                    Select::make('response_type')->label('Jenis Respons')->options(['text' => 'Teks', 'boolean' => 'Ya/Tidak', 'numeric' => 'Numerik', 'choice' => 'Pilihan'])->default('text')->required(),
                    Select::make('response_status')->label('Status Respons')->options(['not_started' => 'Belum Dimulai', 'in_progress' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'verified' => 'Terverifikasi'])->default('not_started')->required(),
                    TextInput::make('score')->label('Skor')->numeric()->minValue(0),
                    Toggle::make('evidence_required')->label('Evidence Wajib')->default(false),
                    TextInput::make('sort_order')->label('Urutan')->numeric()->default(0)->required(),
                    Textarea::make('response')->label('Respons Auditor')->rows(4)->columnSpanFull(),
                    Textarea::make('auditor_notes')->label('Catatan Auditor')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }
}
