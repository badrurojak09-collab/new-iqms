<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\DocumentOutput\Generators\LedDocumentRenderer;
use App\Domain\DocumentOutput\Generators\LkpsSpreadsheetGenerator;
use App\Domain\DocumentOutput\Services\AccreditationDocumentExporter;
use App\Models\Accreditation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AccreditationExportController extends Controller
{
    public function __construct(
        private readonly AccreditationDocumentExporter $exporter,
        private readonly LedDocumentRenderer $ledRenderer,
        private readonly LkpsSpreadsheetGenerator $lkpsGenerator,
    ) {}

    public function export(Request $request, Accreditation $accreditation, string $type): Response|StreamedResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isSuperAdmin() && ! $user->can('view accreditation') && ! $user->can('manage accreditation'))) {
            abort(403, 'Anda tidak memiliki akses untuk mengekspor dokumen akreditasi ini.');
        }

        $code = str_replace(['/', '\\', ' '], '-', $accreditation->code);

        return match ($type) {
            // ── HTML Renderers ──────────────────────────────────────────────
            'led-html' => response($this->exporter->exportLedHtml($accreditation))
                ->header('Content-Type', 'text/html; charset=UTF-8'),

            'lkps-html' => response($this->exporter->exportLkpsHtml($accreditation))
                ->header('Content-Type', 'text/html; charset=UTF-8'),

            'score-simulation' => response($this->exporter->exportScoreSimulationHtml($accreditation))
                ->header('Content-Type', 'text/html; charset=UTF-8'),

            'evidence-matrix-html' => response($this->exporter->exportEvidenceMatrixHtml($accreditation))
                ->header('Content-Type', 'text/html; charset=UTF-8'),

            // ── Native Word .docx ───────────────────────────────────────────
            'led-docx' => response()->streamDownload(
                function () use ($accreditation): void {
                    echo $this->ledRenderer->generate($accreditation);
                },
                "LED-{$code}.docx",
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'Content-Disposition' => "attachment; filename=\"LED-{$code}.docx\"",
                ],
            ),

            // ── Native Excel .xlsx ──────────────────────────────────────────
            'lkps-xlsx' => response()->streamDownload(
                function () use ($accreditation): void {
                    echo $this->lkpsGenerator->generate($accreditation);
                },
                "LKPS-{$code}.xlsx",
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"LKPS-{$code}.xlsx\"",
                ],
            ),

            // ── CSV Fallbacks ────────────────────────────────────────────────
            'lkps-csv' => response()->streamDownload(function () use ($accreditation): void {
                echo $this->exporter->exportLkpsCsv($accreditation);
            }, "LKPS-{$code}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]),

            'evidence-matrix-csv' => response()->streamDownload(function () use ($accreditation): void {
                $adapter = $this->exporter->resolveAdapter($accreditation);
                $data = $adapter->buildEvidenceMatrixData($accreditation);
                $fp = fopen('php://output', 'w');
                fputcsv($fp, ['Butir Respons', 'Kode Bukti', 'Judul Bukti', 'Jenis Relasi', 'Halaman/Bab', 'Catatan Sitasi', 'Kewajiban', 'Status Verifikasi', 'Tautan Cloud']);
                foreach ($data['rows'] as $row) {
                    fputcsv($fp, [
                        $row['response_key'],
                        $row['evidence_code'],
                        $row['evidence_title'],
                        $row['relation_type'],
                        $row['citation_page'],
                        $row['citation_note'],
                        $row['is_required'],
                        $row['verification_status'],
                        $row['external_url'],
                    ]);
                }
                fclose($fp);
            }, "Evidence-Matrix-{$code}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]),

            default => abort(404, 'Jenis ekspor dokumen tidak dikenali.'),
        };
    }
}
