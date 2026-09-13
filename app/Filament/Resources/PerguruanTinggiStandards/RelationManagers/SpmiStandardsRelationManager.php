<?php

declare(strict_types=1);

namespace App\Filament\Resources\PerguruanTinggiStandards\RelationManagers;

use App\Models\SpmiStandard;
use App\Support\Tenancy\TenantQuery;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class SpmiStandardsRelationManager extends RelationManager
{
    protected static string $relationship = 'spmiStandards';
    protected static ?string $title = 'Standar SPMI Turunan';
    protected static ?string $label = 'Standar SPMI';
    protected static ?string $pluralLabel = 'Standar SPMI';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('spmi_framework_id')->label('Framework SPMI')->relationship('framework', 'name', modifyQueryUsing: fn(Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user()))->searchable()->preload()->required(),
            TextInput::make('code')->label('Kode Standar SPMI')->required()->alphaDash()->maxLength(50),
            TextInput::make('name')->label('Nama Standar SPMI')->required()->maxLength(255),
            Select::make('status')->label('Status')->options(['draft' => 'Draf', 'active' => 'Aktif', 'archived' => 'Diarsipkan'])->default('draft')->required(),
            TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            Textarea::make('statement')->label('Pernyataan Standar')->rows(4)->columnSpanFull(),
            Textarea::make('basis')->label('Dasar Standar')->rows(3)->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query): Builder => $query->with(['framework', 'indicators']))
            ->headerActions([
                CreateAction::make()->label('Tambah Standar SPMI')->mutateFormDataUsing(function (array $data): array {
                    $data['perguruan_tinggi_id'] = $this->ownerRecord->perguruan_tinggi_id;
                    $data['perguruan_tinggi_standard_id'] = $this->ownerRecord->getKey();
                    return $data;
                }),
            ])
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Standar')->searchable()->sortable()->wrap(),
                TextColumn::make('framework.name')->label('Framework SPMI')->placeholder('—'),
                TextColumn::make('indicators_count')->counts('indicators')->label('Indikator')->sortable(),
                TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn(?string $state): string => match ($state) {
                    'draft' => 'Draf',
                    'active' => 'Aktif',
                    'archived' => 'Diarsipkan',
                    default => (string) $state,
                }),
            ])
            ->recordActions([EditAction::make()->label('Edit')]);
    }
}
