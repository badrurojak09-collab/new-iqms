<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentGenerationRequests\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DocumentGenerationRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Permintaan Dokumen')->schema([
                TextInput::make('definition.name')->label('Jenis Dokumen')->disabled(),
                TextInput::make('status')->label('Status')->disabled(),
                TextInput::make('period_label')->label('Periode')->disabled(),
                TextInput::make('perguruanTinggi.nama_pt')->label('Perguruan Tinggi')->disabled(),
                TextInput::make('programStudi.nama_prodi')->label('Program Studi')->disabled(),
                Textarea::make('error_message')->label('Pesan Error')->disabled()->columnSpanFull(),
            ])->columns(2),
        ]);
    }
}
