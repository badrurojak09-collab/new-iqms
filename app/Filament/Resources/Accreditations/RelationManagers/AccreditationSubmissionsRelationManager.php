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

class AccreditationSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Paket Pengajuan Akreditasi')
                ->description('Kelola versi paket pengajuan, status review, hash integritas, dan catatan pengajuan.')
                ->icon('heroicon-o-archive-box')
                ->columns(2)
                ->schema([
                    TextInput::make('submission_no')->label('Nomor Pengajuan')->numeric()->integer()->disabled(),
                    TextInput::make('package_hash')->label('Hash Integritas Paket')->maxLength(64)->disabled(),
                    TextInput::make('status')->label('Status Pengajuan')->disabled(),
                    TextInput::make('submitted_at')->label('Diajukan Pada')->disabled(),
                    Textarea::make('notes')->label('Catatan Pengajuan')->rows(4)->disabled()->columnSpanFull(),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('submission_no')->label('Nomor Pengajuan')->sortable(),
            TextColumn::make('status')->label('Status Pengajuan')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('package_hash')->label('Hash Paket')->limit(20)->copyable()->placeholder('Belum Dibuat'),
            TextColumn::make('submitted_at')->label('Diajukan Pada')->dateTime()->placeholder('—')->sortable(),
            TextColumn::make('created_at')->label('Dibuat Pada')->dateTime()->sortable(),
        ])->defaultSort('submission_no', 'desc')
            ->recordActions([
                ViewAction::make()->label('Lihat Paket')->form([
                    TextInput::make('submission_no')->label('Nomor Pengajuan')->disabled(),
                    TextInput::make('package_hash')->label('Hash Integritas Paket')->disabled(),
                    TextInput::make('status')->label('Status Pengajuan')->disabled(),
                    TextInput::make('submitted_at')->label('Diajukan Pada')->disabled(),
                    Textarea::make('notes')->label('Catatan Pengajuan')->rows(4)->disabled()->columnSpanFull(),
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
