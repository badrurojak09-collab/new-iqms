<?php declare(strict_types=1);

namespace App\Filament\Resources\Accreditations;

use App\Domain\Accreditation\ReadinessScoringService;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationAssessmentsRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationDecisionsRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationReadinessItemsRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationResponsesRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationScoreSnapshotsRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationSectionsRelationManager;
use App\Filament\Resources\Accreditations\RelationManagers\AccreditationSubmissionsRelationManager;
use App\Models\Accreditation;
use App\Models\ReadinessRun;
use App\Support\Tenancy\TenantContext;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class AccreditationResource extends Resource
{
    protected static ?string $model = Accreditation::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Akreditasi';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Kegiatan Akreditasi';

    protected static ?string $modelLabel = 'Kegiatan Akreditasi';

    protected static ?string $pluralModelLabel = 'Kegiatan Akreditasi';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kegiatan Akreditasi')
                ->description('Kelola kegiatan akreditasi institusi atau program studi berdasarkan versi instrumen yang dipilih.')
                ->icon('heroicon-o-academic-cap')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Akreditasi')
                        ->required()
                        ->maxLength(100)
                        ->alphaDash()
                        ->unique(ignoreRecord: true),
                    Select::make('scope_type')
                        ->label('Lingkup Akreditasi')
                        ->options([
                            'institution' => 'Institusi',
                            'program_studi' => 'Program Studi'
                        ])
                        ->required()
                        ->live(),
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship(
                            name: 'perguruanTinggi',
                            titleAttribute: 'nama_pt',
                            modifyQueryUsing: function (Builder $query) {
                                $user = auth()->user();
                                if (!$user || $user->isSuperAdmin()) {
                                    return $query;
                                }
                                // Filter dropdown PT hanya yang diizinkan untuk user ini
                                return $query->whereIn('id', $user->accessiblePerguruanTinggiIds());
                            }
                        )
                        ->required()
                        ->default(fn(): ?int => app(TenantContext::class)->perguruanTinggiId())
                        ->live(),
                    Select::make('program_studi_id')
                        ->label('Program Studi')
                        ->relationship(
                            name: 'programStudi',
                            titleAttribute: 'nama_prodi',
                            modifyQueryUsing: function (Builder $query, callable $get) {
                                $user = auth()->user();

                                // Jika Perguruan Tinggi sudah dipilih di form, utamakan filter berdasarkan PT tsb
                                if ($ptId = $get('perguruan_tinggi_id')) {
                                    $query->where('perguruan_tinggi_id', $ptId);
                                }

                                if (!$user || $user->isSuperAdmin()) {
                                    return $query;
                                }

                                // Batasi pilihan prodi sesuai yang diizinkan di Trait HasTenantScope
                                return $query->whereIn('id', $user->accessibleProgramStudiIds());
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    Select::make('instrument_version_id')->label('Versi Instrumen')->relationship('instrumentVersion', 'version_label')->searchable()->preload()->required(),
                    TextInput::make('title')->label('Judul Kegiatan')->required()->maxLength(255),
                    Select::make('status')->label('Status Akreditasi')->options(['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'review' => 'Dalam Review', 'ready' => 'Siap Diajukan', 'submitted' => 'Sudah Diajukan', 'completed' => 'Selesai'])->required()->default('draft'),
                    DatePicker::make('planned_submission_date')->label('Rencana Pengajuan')->native(false),
                    DatePicker::make('submitted_at')->label('Tanggal Pengajuan')->native(false),
                    DatePicker::make('decision_date')->label('Tanggal Keputusan')->native(false),
                    TextInput::make('decision_result')->label('Hasil Keputusan')->maxLength(100),
                    Select::make('owner_id')->label('Penanggung Jawab')->relationship('owner', 'name')->searchable()->preload()->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')
                ->label('Kode Akreditasi')
                ->searchable()
                ->sortable()
                ->copyable(),
            TextColumn::make('title')
                ->label('Judul Kegiatan')
                ->searchable()
                ->wrap(),
            TextColumn::make('perguruanTinggi.nama_pt')
                ->label('Perguruan Tinggi')
                ->sortable()
                ->searchable(),
            TextColumn::make('programStudi.nama_prodi')
                ->label('Program Studi')
                ->sortable()
                ->searchable()
                ->placeholder('Institusi'),
            TextColumn::make('instrumentVersion.version_label')
                ->label('Versi Instrumen')
                ->sortable(),
            TextColumn::make('scope_type')
                ->label('Lingkup')
                ->formatStateUsing(fn(?string $state): string => $state === 'program_studi' ? 'Program Studi' : 'Institusi')
                ->badge(),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->formatStateUsing(fn(mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('planned_submission_date')
                ->label('Rencana Pengajuan')
                ->date()
                ->sortable(),
        ])->recordActions([
            Action::make('calculateReadiness')
                ->label('Hitung Kesiapan')
                ->icon(Heroicon::OutlinedCalculator)
                ->requiresConfirmation()
                ->visible(fn(): bool => auth()->user()?->isSuperAdmin() || auth()->user()?->can('manage accreditation') || auth()->user()?->can('review accreditation'))
                ->action(fn(Accreditation $record): ReadinessRun => app(ReadinessScoringService::class)->calculate(auth()->user(), $record)),
        ])->filters([
            SelectFilter::make('status')->label('Status')->options(['draft' => 'Draf', 'in_progress' => 'Sedang Berjalan', 'review' => 'Dalam Review', 'ready' => 'Siap Diajukan', 'submitted' => 'Sudah Diajukan', 'completed' => 'Selesai']),
            SelectFilter::make('scope_type')->options(['institution' => 'Institusi', 'program_studi' => 'Program Studi']),
        ])->defaultSort('planned_submission_date');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with([
            'perguruanTinggi',
            'programStudi',
            'instrumentVersion'
        ]);

        $user = auth()->user();

        // Jika Super Admin, jangan filter apapun
        if (!$user || $user->isSuperAdmin()) {
            return $query;
        }

        // Ambil SEMUA ID PT yang berhak diakses (berasal dari Trait HasTenantScope)
        $allowedPtIds = $user->accessiblePerguruanTinggiIds();

        return $query->whereIn('perguruan_tinggi_id', $allowedPtIds);
    }

    public static function getRelations(): array
    {
        return [
            AccreditationScoreSnapshotsRelationManager::class,
            AccreditationSectionsRelationManager::class,
            AccreditationResponsesRelationManager::class,
            AccreditationReadinessItemsRelationManager::class,
            AccreditationAssessmentsRelationManager::class,
            AccreditationSubmissionsRelationManager::class,
            AccreditationDecisionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccreditations::route('/'),
            'create' => Pages\CreateAccreditation::route('/create'),
            'edit' => Pages\EditAccreditation::route('/{record}/edit'),
        ];
    }
}
