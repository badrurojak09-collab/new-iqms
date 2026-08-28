<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationScoreSnapshotsRelationManager extends RelationManager
{
    protected static string $relationship = 'scoreSnapshots';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Snapshot Skor Akreditasi')
                ->description('Riwayat hasil perhitungan yang disimpan permanen dan tidak dapat diubah.')
                ->icon('heroicon-o-archive-box')
                ->columns(2)
                ->schema([
                    TextInput::make('score')->label('Skor')->disabled(),
                    TextInput::make('status')->label('Status Snapshot')->disabled(),
                    TextInput::make('snapshot_hash')->label('Hash Integritas Snapshot')->disabled(),
                    TextInput::make('calculated_at')->label('Dihitung Pada')->disabled(),
                    Textarea::make('rule_results')->label('Hasil Aturan')->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $state)->disabled()->columnSpanFull(),
                    Textarea::make('input_snapshot')->label('Snapshot Input')->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $state)->disabled()->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('calculated_at')->label('Dihitung Pada')->dateTime()->sortable(),
                TextColumn::make('score')->numeric(decimalPlaces: 4)->sortable(),
                TextColumn::make('status')->label('Status Snapshot')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
                TextColumn::make('instrumentVersion.version_label')->label('Versi Instrumen')->sortable(),
                TextColumn::make('calculatedBy.name')->label('Dihitung Oleh')->placeholder('Sistem'),
                TextColumn::make('snapshot_hash')->label('Hash Integritas')->limit(18)->copyable()->copyMessage('Hash disalin'),
            ])
            ->defaultSort('calculated_at', 'desc')
            ->actions([
                ViewAction::make()->label('Lihat Snapshot')->form([
                    TextInput::make('score')->label('Skor')->disabled(),
                    TextInput::make('status')->label('Status Snapshot')->disabled(),
                    TextInput::make('snapshot_hash')->label('Hash Integritas Snapshot')->disabled(),
                    TextInput::make('calculated_at')->label('Dihitung Pada')->disabled(),
                    Textarea::make('rule_results')->label('Hasil Aturan')->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $state)->disabled()->columnSpanFull(),
                    Textarea::make('input_snapshot')->label('Snapshot Input')->formatStateUsing(fn ($state): string => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : (string) $state)->disabled()->columnSpanFull(),
                ]),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
