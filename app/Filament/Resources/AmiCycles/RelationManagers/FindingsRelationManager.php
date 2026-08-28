<?php

declare(strict_types=1);

namespace App\Filament\Resources\AmiCycles\RelationManagers;

use App\Models\AmiChecklistItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';

    protected static ?string $title = 'Temuan AMI';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('classification')->label('Klasifikasi')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'nonconformity' => 'Ketidaksesuaian',
                    'opportunity' => 'Peluang Perbaikan',
                    default => 'Observasi',
                })->badge(),
                TextColumn::make('severity')->label('Keparahan')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'major' => 'Mayor',
                    'minor' => 'Minor',
                    default => 'Sedang',
                })->badge(),
                TextColumn::make('condition')->label('Kondisi')->wrap()->limit(80),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'in_progress' => 'Dalam Tindak Lanjut',
                    'closed' => 'Ditutup',
                    default => 'Terbuka',
                })->badge(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Temuan')
                    ->visible(fn (): bool => auth()->user()?->can('manage ami') || auth()->user()?->can('review ami'))
                    ->form([
                        TextInput::make('code')->label('Kode Temuan')->required()->maxLength(80),
                        Select::make('ami_checklist_item_id')->label('Checklist Terkait')->options(fn (): array => AmiChecklistItem::query()->where('ami_cycle_id', $this->getOwnerRecord()->getKey())->orderBy('code')->pluck('code', 'id')->all())->searchable()->preload(),
                        Select::make('classification')->label('Klasifikasi')->options(['observation' => 'Observasi', 'nonconformity' => 'Ketidaksesuaian', 'opportunity' => 'Peluang Perbaikan'])->default('observation')->required(),
                        Select::make('severity')->label('Tingkat Keparahan')->options(['low' => 'Rendah', 'medium' => 'Sedang', 'minor' => 'Minor', 'major' => 'Mayor'])->default('medium')->required(),
                        Textarea::make('condition')->label('Kondisi/Temuan')->required()->rows(4)->columnSpanFull(),
                        Textarea::make('requirement')->label('Persyaratan')->rows(3)->columnSpanFull(),
                        Textarea::make('criteria')->label('Kriteria')->rows(3)->columnSpanFull(),
                        Textarea::make('cause')->label('Akar Penyebab')->rows(3)->columnSpanFull(),
                        Textarea::make('impact')->label('Dampak')->rows(3)->columnSpanFull(),
                        Textarea::make('recommendation')->label('Rekomendasi')->rows(4)->columnSpanFull(),
                        Select::make('status')->label('Status Temuan')->options(['open' => 'Terbuka', 'in_progress' => 'Dalam Tindak Lanjut', 'closed' => 'Ditutup'])->default('open')->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['reported_by'] = auth()->id();
                        return $data;
                    }),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Edit')->visible(fn (): bool => auth()->user()?->can('manage ami') || auth()->user()?->can('review ami')),
                \Filament\Actions\DeleteAction::make()->label('Hapus')->visible(fn (): bool => auth()->user()?->can('manage ami')),
            ])
            ->toolbarActions([]);
    }
}
