<?php declare(strict_types=1);

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\UserTenantScope;
use App\Models\Yayasan;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class TenantScopesRelationManager extends RelationManager
{
    protected static string $relationship = 'tenantScopes';

    protected static ?string $title = 'Lingkup Akses dan Peran';

    protected static ?string $modelLabel = 'Lingkup Akses';

    protected static ?string $pluralModelLabel = 'Lingkup Akses';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Lingkup Akses Pengguna')
                ->description('Tentukan organisasi, peran, dan masa berlaku akses pengguna ini.')
                ->icon('heroicon-o-shield-check')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('role_id')
                        ->label('Peran pada Lingkup Ini')
                        ->relationship('role', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('scope_type')
                        ->label('Tipe Lingkup')
                        ->options([
                            'yayasan' => 'Yayasan',
                            'perguruan_tinggi' => 'Perguruan Tinggi',
                            'program_studi' => 'Program Studi',
                        ])
                        ->live()
                        ->required()
                        ->afterStateUpdated(fn(callable $set): mixed => $set('scope_id', null)),
                    Select::make('scope_id')
                        ->label('Organisasi/Lingkup')
                        ->options(fn(callable $get): array => match ($get('scope_type')) {
                            'yayasan' => Yayasan::query()->orderBy('nama')->pluck('nama', 'id')->all(),
                            'perguruan_tinggi' => PerguruanTinggi::query()->orderBy('nama_pt')->pluck('nama_pt', 'id')->all(),
                            'program_studi' => ProgramStudi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->all(),
                            default => [],
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                    Toggle::make('is_default')
                        ->label('Jadikan Lingkup Default')
                        ->helperText('Gunakan konteks ini sebagai lingkup awal saat pengguna login.'),
                    DatePicker::make('starts_at')
                        ->label('Mulai Berlaku'),
                    DatePicker::make('ends_at')
                        ->label('Berakhir')
                        ->afterOrEqual('starts_at'),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('scope_type')
            ->columns([
                TextColumn::make('scope_type')
                    ->label('Tipe Lingkup')
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'yayasan' => 'Yayasan',
                        'perguruan_tinggi' => 'Perguruan Tinggi',
                        'program_studi' => 'Program Studi',
                        default => $state ?: '—',
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('scope_label')
                    ->label('Lingkup')
                    ->state(fn(UserTenantScope $record): string => $record->scopeLabel())
                    ->wrap(),
                TextColumn::make('role.name')
                    ->label('Peran')
                    ->badge()
                    ->sortable(),
                TextColumn::make('is_default')
                    ->label('Default')
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Ya' : 'Tidak')
                    ->badge(),
                TextColumn::make('starts_at')->label('Mulai Berlaku')->date('d/m/Y'),
                TextColumn::make('ends_at')->label('Berakhir')->date('d/m/Y')->placeholder('Tidak dibatasi'),
            ])
            ->headerActions([
                CreateAction::make()->label('Tambah Lingkup'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit'),
                DeleteAction::make()->label('Hapus'),
            ])
            ->defaultSort('is_default', 'desc');
    }
}
