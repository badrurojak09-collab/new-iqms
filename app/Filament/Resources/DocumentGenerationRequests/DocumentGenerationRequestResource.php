<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentGenerationRequests;

use App\Filament\Resources\DocumentGenerationRequests\Pages\ListDocumentGenerationRequests;
use App\Filament\Resources\DocumentGenerationRequests\Schemas\DocumentGenerationRequestForm;
use App\Filament\Resources\DocumentGenerationRequests\Tables\DocumentGenerationRequestsTable;
use App\Models\DocumentGenerationRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class DocumentGenerationRequestResource extends Resource
{
    protected static ?string $model = DocumentGenerationRequest::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Reporting';
    protected static ?string $navigationLabel = 'Riwayat Permintaan Dokumen';
    protected static ?string $modelLabel = 'riwayat permintaan dokumen';
    protected static ?string $pluralModelLabel = 'riwayat permintaan dokumen';
    protected static ?int $navigationSort = 20;
    protected static string|BackedEnum|null $navigationIcon = null;

    public static function form(Schema $schema): Schema { return DocumentGenerationRequestForm::configure($schema); }
    public static function table(Table $table): Table { return DocumentGenerationRequestsTable::configure($table); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function getPages(): array { return ['index' => ListDocumentGenerationRequests::route('/')]; }
}
