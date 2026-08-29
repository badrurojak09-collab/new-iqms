<?php declare(strict_types=1);

namespace App\Filament\Resources\DocumentDefinitions\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DocumentDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Definisi Dokumen')
                ->description('Daftarkan jenis output dokumen tanpa mengunci template final.')
                ->icon('heroicon-o-document-text')
                ->schema([
                    TextInput::make('code')->label('Kode Dokumen')->required()->maxLength(100)->unique(ignoreRecord: true),
                    TextInput::make('name')->label('Nama Dokumen')->required()->maxLength(255),
                    Select::make('domain')->label('Sumber Modul')->options([
                        'spmi' => 'SPMI',
                        'ami' => 'AMI',
                        'rtm' => 'RTM',
                        'rtl' => 'RTL',
                        'accreditation' => 'Akreditasi',
                        'reporting' => 'Reporting',
                    ])->required(),
                    Select::make('scope_type')->label('Tingkat Scope')->options([
                        'yayasan' => 'Yayasan',
                        'perguruan_tinggi' => 'Perguruan Tinggi',
                        'upps' => 'UPPS/Unit Pengelola',
                        'program_studi' => 'Program Studi',
                    ])->placeholder('Tidak dibatasi'),
                    CheckboxList::make('supported_formats')->label('Format yang Didukung')->options([
                        'pdf' => 'PDF',
                        'docx' => 'Word (DOCX)',
                        'xlsx' => 'Excel (XLSX)',
                        'html' => 'Pratinjau HTML',
                    ])->columns(4)->required(),
                    Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),
                    Toggle::make('is_active')->label('Aktif')->default(true),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ]);
    }
}
