<?php

declare(strict_types=1);

namespace App\Filament\Resources\RtmMeetings\RelationManagers;

use App\Models\AmiFinding;
use App\Models\ReadinessGap;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DecisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'decisions';

    protected static ?string $title = 'Keputusan RTM';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('decision')->label('Keputusan')->wrap()->limit(100),
                TextColumn::make('finding.code')->label('Temuan AMI')->placeholder('—'),
                TextColumn::make('status')->label('Status')->formatStateUsing(fn (?string $state): string => match ($state) {
                    'closed' => 'Ditutup',
                    'draft' => 'Draf',
                    default => 'Disetujui',
                })->badge(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()->label('Tambah Keputusan')->visible(fn (): bool => auth()->user()?->can('manage rtm') ?? false)->form([
                    TextInput::make('code')->label('Kode Keputusan')->required()->maxLength(80),
                    Select::make('ami_finding_id')->label('Temuan AMI Terkait')->options(fn (): array => AmiFinding::query()->where('ami_cycle_id', $this->getOwnerRecord()->ami_cycle_id)->orderBy('code')->get()->mapWithKeys(fn (AmiFinding $finding): array => [$finding->id => $finding->code.' — '.str($finding->condition)->limit(80)])->all())->searchable()->preload(),
                    Select::make('readiness_gap_id')->label('Gap Kesiapan Terkait')->options(fn (): array => ReadinessGap::query()->orderByDesc('id')->get()->mapWithKeys(fn (ReadinessGap $gap): array => [$gap->id => ($gap->item_key ?: 'Gap').' — '.str($gap->description ?: 'Tanpa deskripsi')->limit(80)])->all())->searchable()->preload(),
                    Textarea::make('decision')->label('Isi Keputusan')->required()->rows(4)->columnSpanFull(),
                    Textarea::make('rationale')->label('Dasar Pertimbangan')->rows(4)->columnSpanFull(),
                    Select::make('status')->label('Status Keputusan')->options(['draft' => 'Draf', 'approved' => 'Disetujui', 'closed' => 'Ditutup'])->default('approved')->required(),
                ]),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()->label('Edit'),
                \Filament\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([]);
    }
}
