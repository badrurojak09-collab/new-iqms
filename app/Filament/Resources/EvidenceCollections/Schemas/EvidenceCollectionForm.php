<?php

declare(strict_types=1);

namespace App\Filament\Resources\EvidenceCollections\Schemas;

use App\Support\Tenancy\TenantQuery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EvidenceCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Koleksi Evidence')
                ->description('Kelola kumpulan persyaratan evidence untuk akreditasi atau audit dengan tautan cloud institusi.')
                ->icon('heroicon-o-folder-open')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('perguruan_tinggi_id')
                        ->label('Perguruan Tinggi')
                        ->relationship(
                            'perguruanTinggi',
                            'nama_pt',
                            modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forPerguruanTinggi($query, auth()->user())
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('program_studi_id')
                        ->label('Program Studi')
                        ->relationship(
                            'programStudi',
                            'nama_prodi',
                            modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forProgramStudi($query, auth()->user())
                        )
                        ->searchable()
                        ->preload()
                        ->helperText('Kosongkan untuk koleksi tingkat perguruan tinggi.'),
                    Select::make('accreditation_id')
                        ->label('Kegiatan Akreditasi')
                        ->relationship(
                            'accreditation',
                            'title',
                            modifyQueryUsing: fn (Builder $query): Builder => TenantQuery::forOptionalProgramStudi($query, auth()->user())
                        )
                        ->searchable()
                        ->preload(),
                    TextInput::make('code')->label('Kode Koleksi')->required()->maxLength(100)->alphaDash(),
                    TextInput::make('name')->label('Nama Koleksi')->required()->maxLength(255),
                    Select::make('provider')->label('Penyedia Cloud')->options(['google_drive' => 'Google Drive', 'sharepoint' => 'SharePoint/OneDrive', 'dropbox' => 'Dropbox', 'institution_cloud' => 'Cloud Institusi'])->default('google_drive')->required(),
                    TextInput::make('root_folder_url')->label('Tautan Folder Utama')->url()->rules(['url', 'regex:/^https:\/\//i'])->maxLength(2000)->helperText('File tetap berada di cloud institusi; SQM hanya menyimpan tautannya.'),
                    TextInput::make('root_folder_id')->label('ID Folder Utama')->maxLength(255),
                    Select::make('status')->label('Status Koleksi')->options(['draft' => 'Draf', 'active' => 'Aktif', 'approved' => 'Disetujui', 'locked' => 'Dikunci', 'archived' => 'Diarsipkan'])->default('draft')->required(),
                    Textarea::make('description')->label('Deskripsi')->rows(4)->columnSpanFull(),
                ]),
        ]);
    }
}
