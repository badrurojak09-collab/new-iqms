<?php

declare(strict_types=1);

namespace App\Filament\Resources\Evidences\RelationManagers;

use App\Support\Ui\StatusLabel;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvidenceReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    protected static ?string $title = 'Riwayat Review Evidence';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('status')->label('Status Review')->disabled(),
            TextInput::make('reviewer.name')->label('Reviewer')->disabled(),
            TextInput::make('reviewed_at')->label('Ditinjau Pada')->disabled(),
            Textarea::make('notes')->label('Catatan Reviewer')->disabled()->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewed_at')->label('Ditinjau Pada')->dateTime()->sortable(),
                TextColumn::make('reviewer.name')->label('Reviewer')->placeholder('Sistem'),
                TextColumn::make('status')->label('Status Review')->badge()->formatStateUsing(fn (mixed $state): string => StatusLabel::for($state))->sortable(),
                TextColumn::make('evidenceVersion.version_no')->label('Nomor Versi')->sortable(),
                TextColumn::make('notes')->label('Catatan Reviewer')->limit(60)->wrap(),
            ])
            ->defaultSort('reviewed_at', 'desc')
            ->actions([
                ViewAction::make()->label('Lihat Review')->form([
                    TextInput::make('status')->label('Status Review')->disabled(),
                    TextInput::make('reviewer.name')->label('Reviewer')->disabled(),
                    TextInput::make('reviewed_at')->label('Ditinjau Pada')->disabled(),
                    Textarea::make('notes')->label('Catatan Reviewer')->disabled()->columnSpanFull(),
                ]),
            ])
            ->headerActions([])
            ->toolbarActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
