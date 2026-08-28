<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiFindings\Schemas;

use App\Models\AmiChecklistItem;
use App\Models\AmiCycle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiFindingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Temuan Audit Mutu Internal')
                ->description('Dokumentasikan temuan AMI, klasifikasi, analisis penyebab, dampak, dan rekomendasi perbaikannya.')
                ->icon('heroicon-o-exclamation-triangle')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('ami_cycle_id')
                        ->label('Siklus AMI')
                        ->options(fn (): array => AmiCycle::query()->latest('period_year')->get()->mapWithKeys(fn (AmiCycle $cycle): array => [$cycle->id => $cycle->code.' — '.$cycle->name.' — '.$cycle->period_year])->all())
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('ami_checklist_item_id')
                        ->label('Checklist Terkait')
                        ->options(fn (): array => AmiChecklistItem::query()->with('cycle')->latest('id')->get()->mapWithKeys(fn (AmiChecklistItem $item): array => [$item->id => $item->code.' — '.str($item->question)->limit(90)])->all())
                        ->searchable()
                        ->preload()
                        ->helperText('Opsional. Pilih checklist yang menjadi sumber temuan.'),
                    TextInput::make('code')->label('Kode Temuan')->required()->maxLength(80),
                    Select::make('classification')->label('Klasifikasi')->options(['observation' => 'Observasi', 'nonconformity' => 'Ketidaksesuaian', 'opportunity' => 'Peluang Perbaikan'])->default('observation')->required(),
                    Select::make('severity')->label('Tingkat Keparahan')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'minor' => 'Minor', 'major' => 'Mayor'])->default('medium')->required(),
                    Select::make('status')->label('Status Temuan')->options(['open' => 'Terbuka', 'in_progress' => 'Dalam Tindak Lanjut', 'closed' => 'Ditutup'])->default('open')->required(),
                    Textarea::make('condition')->label('Kondisi/Temuan')->required()->rows(5)->columnSpanFull(),
                    Textarea::make('requirement')->label('Persyaratan')->rows(3)->columnSpanFull(),
                    Textarea::make('criteria')->label('Kriteria')->rows(3)->columnSpanFull(),
                    Textarea::make('cause')->label('Akar Penyebab')->rows(3)->columnSpanFull(),
                    Textarea::make('impact')->label('Dampak')->rows(3)->columnSpanFull(),
                    Textarea::make('recommendation')->label('Rekomendasi')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }
}
