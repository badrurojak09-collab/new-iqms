<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

final class AccreditationReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows->values();
    }

    public function headings(): array
    {
        return ['Kode', 'Judul', 'Scope', 'Program Studi', 'Versi Instrumen', 'Status', 'Sections', 'Progress LED (%)', 'Progress LKPS (%)', 'Responses', 'Responses Selesai', 'Readiness Items', 'Readiness Selesai', 'Rencana Submit', 'Hasil Keputusan'];
    }

    public function title(): string
    {
        return 'Accreditation Report';
    }
}
