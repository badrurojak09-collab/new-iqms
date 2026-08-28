<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use App\Models\AccreditationSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'responses';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('accreditation_section_id')->label('Bagian')->options(fn (): array => AccreditationSection::query()->where('accreditation_id', $this->ownerRecord->getKey())->orderBy('sort_order')->pluck('title', 'id')->all())->searchable()->preload()->required(),
            TextInput::make('response_key')->required()->maxLength(120),
            Select::make('response_type')->options(['text' => 'Text', 'numeric' => 'Numeric', 'json' => 'JSON'])->required()->default('text'),
            Textarea::make('response_text')->label('Respons')->columnSpanFull(),
            TextInput::make('response_numeric')->numeric(),
            Select::make('status')->options(['draft' => 'Draf', 'submitted' => 'Submitted', 'verified' => 'Verified', 'rejected' => 'Rejected'])->required()->default('draft'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('response_key')->label('Kunci Respons')->searchable()->sortable(),
            TextColumn::make('section.title')->label('Bagian')->wrap(),
            TextColumn::make('response_type')->label('Jenis Respons')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'text' => 'Teks',
                'numeric' => 'Angka',
                'json' => 'JSON',
                default => (string) $state,
            }),
            TextColumn::make('status')->label('Status Respons')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('submitted_at')->label('Dikirim Pada')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc');
    }
}
