<?php

declare(strict_types=1);

namespace App\Filament\Resources\LedTemplates\RelationManagers;

use App\Models\InstrumentNode;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LedTemplateSectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Bagian LED';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode Bagian')->required()->maxLength(100),
            TextInput::make('title')->label('Judul Bagian')->required()->maxLength(255),
            Select::make('instrument_node_id')->label('Simpul Instrumen')->options(fn (): array => InstrumentNode::query()->where('instrument_version_id', $this->ownerRecord->instrument_version_id)->orderBy('sort_order')->pluck('title', 'id')->all())->searchable()->preload(),
            Textarea::make('guidance')->label('Panduan Pengisian')->columnSpanFull(),
            Toggle::make('is_required')->default(false),
            TextInput::make('sort_order')->label('Urutan Tampilan')->numeric()->integer()->default(0),
            KeyValue::make('validation_rules')->label('Aturan Validasi')->keyLabel('Kunci')->valueLabel('Nilai')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
            TextColumn::make('code')->label('Kode')->searchable()->sortable(),
            TextColumn::make('title')->label('Judul Bagian')->searchable()->wrap(),
            TextColumn::make('instrumentNode.code')->label('Kode Simpul Instrumen')->sortable(),
            TextColumn::make('is_required')
                ->label('Wajib')
                ->badge()
                ->formatStateUsing(fn (mixed $state): string => (bool) $state ? 'Ya' : 'Tidak')
                ->color(fn (mixed $state): string => (bool) $state ? 'success' : 'gray'),
        ])->defaultSort('sort_order')->reorderable('sort_order');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['instrument_node_id']) && ! InstrumentNode::query()->whereKey($data['instrument_node_id'])->where('instrument_version_id', $this->ownerRecord->instrument_version_id)->exists()) {
            $data['instrument_node_id'] = null;
        }

        return $data;
    }
}
