<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Reporting\AccreditationReportData;
use App\Exports\AccreditationReportExport;
use App\Models\Accreditation;
use App\Models\ProgramStudi;
use App\Support\Tenancy\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class AccreditationReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static string|\UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?string $navigationLabel = 'Laporan Akreditasi';
    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Laporan Akreditasi';

    protected string $view = 'filament.pages.accreditation-report';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')->label('Export Excel')->form($this->filters())->action(fn (array $data) => Excel::download(new AccreditationReportExport($this->rows($data)), 'accreditation-report.xlsx')),
            Action::make('exportPdf')->label('Export PDF')->form($this->filters())->action(fn (array $data) => response()->streamDownload(fn () => print Pdf::loadView('reports.accreditation', ['rows' => $this->rows($data), 'perguruanTinggiId' => $this->ptId()])->output(), 'accreditation-report.pdf')),
        ];
    }

    protected function filters(): array
    {
        return [
            Select::make('program_studi_id')->label('Program Studi')->options(fn (): array => ProgramStudi::query()->where('perguruan_tinggi_id', $this->ptId())->orderBy('nama_prodi')->pluck('nama_prodi', 'id')->all())->searchable()->nullable(),
            DatePicker::make('from')->label('Dari tanggal'),
            DatePicker::make('until')->label('Sampai tanggal')->afterOrEqual('from'),
        ];
    }

    private function ptId(): int
    {
        $ptId = app(TenantContext::class)->perguruanTinggiId();
        abort_if($ptId === null, 403, 'Tenant Perguruan Tinggi belum dipilih.');
        Gate::authorize('viewAny', Accreditation::class);

        return $ptId;
    }

    private function rows(array $data): Collection
    {
        return app(AccreditationReportData::class)->forPerguruanTinggi($this->ptId(), $data['program_studi_id'] ?? null, $data['from'] ?? null, $data['until'] ?? null);
    }
}
