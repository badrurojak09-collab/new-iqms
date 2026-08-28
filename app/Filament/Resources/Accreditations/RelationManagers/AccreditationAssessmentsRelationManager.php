<?php

declare(strict_types=1);

namespace App\Filament\Resources\Accreditations\RelationManagers;

use App\Models\AccreditationResponse;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccreditationAssessmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assessments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('accreditation_response_id')->label('Respons')->options(fn (): array => AccreditationResponse::query()->where('accreditation_id', $this->ownerRecord->getKey())->orderBy('response_key')->pluck('response_key', 'id')->all())->searchable()->preload()->nullable(),
            Select::make('assessment_type')->options(['internal_review' => 'Internal Review', 'mock_assessment' => 'Mock Assessment', 'final_review' => 'Final Review'])->required()->default('internal_review'),
            Select::make('result')->options(['pass' => 'Pass', 'conditional' => 'Conditional', 'fail' => 'Fail'])->nullable(),
            TextInput::make('score')->numeric()->minValue(0),
            Select::make('status')->options(['draft' => 'Draf', 'completed' => 'Completed'])->required()->default('draft'),
            Textarea::make('notes')->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('assessment_type')->label('Jenis Penilaian')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'internal_review' => 'Tinjauan Internal',
                'mock_assessment' => 'Simulasi Penilaian',
                'final_review' => 'Tinjauan Akhir',
                default => (string) $state,
            }),
            TextColumn::make('response.response_key')->label('Respons')->searchable(),
            TextColumn::make('result')->label('Hasil')->badge()->formatStateUsing(fn (mixed $state): string => match ($state) {
                'pass' => 'Lulus',
                'conditional' => 'Bersyarat',
                'fail' => 'Tidak Lulus',
                default => (string) $state,
            }),
            TextColumn::make('score')->label('Skor')->numeric(),
            TextColumn::make('status')->label('Status Penilaian')->badge()->formatStateUsing(fn (mixed $state): string => \App\Support\Ui\StatusLabel::for($state)),
            TextColumn::make('assessed_at')->label('Dinilai Pada')->dateTime()->sortable(),
        ])->defaultSort('updated_at', 'desc');
    }
}
