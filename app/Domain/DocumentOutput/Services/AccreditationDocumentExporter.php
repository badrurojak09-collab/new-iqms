<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Services;

use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Domain\DocumentOutput\Adapters\BanPtIaptDocumentAdapter;
use App\Domain\DocumentOutput\Adapters\LamInfokom21DocumentAdapter;
use App\Domain\DocumentOutput\Contracts\AccreditationDocumentAdapter;
use App\Models\Accreditation;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AccreditationDocumentExporter
{
    public function __construct(
        private readonly RuntimeScoringEngine $scoringEngine
    ) {}

    public function resolveAdapter(Accreditation $accreditation): AccreditationDocumentAdapter
    {
        $accreditation->loadMissing(['instrumentVersion.family.accreditationBody']);
        $familyCode = $accreditation->instrumentVersion?->family?->code;
        $bodyCode = $accreditation->instrumentVersion?->family?->accreditationBody?->code;

        if (str_contains((string) $familyCode, 'INFOKOM') || str_contains((string) $bodyCode, 'INFOKOM')) {
            return new LamInfokom21DocumentAdapter($this->scoringEngine);
        }

        return new BanPtIaptDocumentAdapter($this->scoringEngine);
    }

    public function exportLedHtml(Accreditation $accreditation): string
    {
        $adapter = $this->resolveAdapter($accreditation);
        $data = $adapter->buildLedData($accreditation);

        $sectionsHtml = '';
        foreach ($data['sections'] as $section) {
            $elementsHtml = '';
            foreach ($section['elements'] as $element) {
                $evidencesList = '';
                if (! empty($element['evidences'])) {
                    $evListItems = '';
                    foreach ($element['evidences'] as $ev) {
                        $citation = $ev['citation_page'] ? " ({$ev['citation_page']})" : '';
                        $linkHtml = $ev['url'] ? " - <a href='{$ev['url']}' target='_blank' style='color:#0284c7;'>[Buka Link Cloud]</a>" : '';
                        $evListItems .= "<li style='margin-bottom:4px;'><strong>[{$ev['code']}] {$ev['title']}</strong>{$citation} <span style='font-size:11px;background:#e0f2fe;color:#0369a1;padding:2px 6px;border-radius:4px;'>{$ev['status']}</span>{$linkHtml}</li>";
                    }
                    $evidencesList = "<div style='margin-top:10px;padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;'><strong style='font-size:12px;color:#475569;'>Dokumen Bukti Tertaut:</strong><ul style='margin:6px 0 0 16px;padding:0;font-size:12px;'>{$evListItems}</ul></div>";
                }

                $elementsHtml .= "
                <div style='margin-bottom:20px;padding:16px;background:#ffffff;border:1px solid #e2e8f0;border-radius:8px;'>
                    <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;'>
                        <h4 style='margin:0;color:#1e293b;font-size:15px;'>[{$element['code']}] {$element['title']}</h4>
                        <span style='font-size:12px;background:#f1f5f9;color:#475569;padding:3px 8px;border-radius:4px;'>Bobot: {$element['weight']}</span>
                    </div>
                    <div style='font-size:13px;color:#334155;line-height:1.6;white-space:pre-wrap;background:#fafafa;padding:12px;border-radius:6px;'>{$element['response_text']}</div>
                    {$evidencesList}
                </div>";
            }

            $sectionsHtml .= "
            <div style='margin-bottom:32px;'>
                <div style='border-bottom:2px solid #0284c7;padding-bottom:6px;margin-bottom:16px;'>
                    <h3 style='margin:0;color:#0f172a;font-size:18px;'>[{$section['code']}] {$section['title']}</h3>
                    <small style='color:#64748b;'>{$section['description']}</small>
                </div>
                {$elementsHtml}
            </div>";
        }

        $institution = htmlspecialchars($data['institution_name']);
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars($data['study_program_name']) . "</h3>" : '';
        $title = htmlspecialchars($data['title']);

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>LED - {$title}</title>
            <style>
                body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
                .container { max-width: 960px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print { body { background: #fff; padding: 0; } .container { box-shadow: none; padding: 0; max-width: 100%; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:960px;margin:0 auto 16px auto;text-align:right;'>
                <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;'>Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:20px;margin-bottom:28px;'>
                    <h1 style='margin:0 0 6px 0;font-size:22px;color:#0f172a;'>LAPORAN EVALUASI DIRI (LED)</h1>
                    <h2 style='margin:0 0 6px 0;font-size:18px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:13px;'>Instrumen: {$data['version_label']} | Kode: {$data['accreditation_code']}</p>
                </div>
                {$sectionsHtml}
                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:40px;font-size:12px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh Antigravity i-QMS pada {$data['generated_at']}.
                </div>
            </div>
        </body>
        </html>";
    }

    public function exportLkpsHtml(Accreditation $accreditation): string
    {
        $adapter = $this->resolveAdapter($accreditation);
        $data = $adapter->buildLkpsData($accreditation);

        $tablesHtml = '';
        foreach ($data['tables'] as $table) {
            $headersHtml = '';
            foreach ($table['headers'] as $header) {
                $headersHtml .= "<th style='background:#f1f5f9;color:#334155;padding:8px 12px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>{$header}</th>";
            }

            $rowsHtml = '';
            foreach ($table['rows'] as $row) {
                $cellsHtml = '';
                foreach ($row as $cell) {
                    $cellsHtml .= "<td style='padding:8px 12px;border:1px solid #e2e8f0;font-size:12px;color:#1e293b;'>{$cell}</td>";
                }
                $rowsHtml .= "<tr>{$cellsHtml}</tr>";
            }

            $tablesHtml .= "
            <div style='margin-bottom:28px;'>
                <h3 style='margin:0 0 4px 0;font-size:15px;color:#0f172a;'>{$table['code']}: {$table['title']}</h3>
                <p style='margin:0 0 10px 0;font-size:12px;color:#64748b;'>{$table['description']}</p>
                <div style='overflow-x:auto;'>
                    <table style='width:100%;border-collapse:collapse;margin-bottom:12px;'>
                        <thead><tr>{$headersHtml}</tr></thead>
                        <tbody>{$rowsHtml}</tbody>
                    </table>
                </div>
            </div>";
        }

        $institution = htmlspecialchars($data['institution_name']);
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars($data['study_program_name']) . "</h3>" : '';

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>LKPS / LKPT - {$accreditation->title}</title>
            <style>
                body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
                .container { max-width: 1040px; margin: 0 auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print { body { background: #fff; padding: 0; } .container { box-shadow: none; padding: 0; max-width: 100%; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:1040px;margin:0 auto 16px auto;text-align:right;'>
                <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;'>Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:20px;margin-bottom:28px;'>
                    <h1 style='margin:0 0 6px 0;font-size:22px;color:#0f172a;'>LAPORAN KINERJA (LKPS / LKPT)</h1>
                    <h2 style='margin:0 0 6px 0;font-size:18px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:13px;'>Tipe: {$data['type']} | Kode: {$data['accreditation_code']}</p>
                </div>
                {$tablesHtml}
                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:12px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh Antigravity i-QMS pada {$data['generated_at']}.
                </div>
            </div>
        </body>
        </html>";
    }

    public function exportLkpsCsv(Accreditation $accreditation): string
    {
        $adapter = $this->resolveAdapter($accreditation);
        $data = $adapter->buildLkpsData($accreditation);

        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, ['Tabel', 'Kode Tabel', 'Nama Kolom / Baris Data']);

        foreach ($data['tables'] as $table) {
            fputcsv($fp, [$table['code'], $table['title'], $table['description']]);
            fputcsv($fp, $table['headers']);
            foreach ($table['rows'] as $row) {
                fputcsv($fp, $row);
            }
            fputcsv($fp, []);
        }

        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return (string) $csv;
    }

    public function exportScoreSimulationHtml(Accreditation $accreditation): string
    {
        $adapter = $this->resolveAdapter($accreditation);
        $data = $adapter->buildScoreSimulationData($accreditation);

        $rulesHtml = '';
        foreach ($data['rules'] as $rule) {
            if (($rule['rule_type'] ?? '') === 'qualification_summary') {
                continue;
            }

            $ruleCode = htmlspecialchars((string) ($rule['code'] ?? ($rule['field'] ?? '-')));
            $ruleName = htmlspecialchars((string) ($rule['name'] ?? ($rule['rule_type'] ?? 'Penilaian')));
            $score = isset($rule['score']) && is_numeric($rule['score']) ? number_format((float) $rule['score'], 2) : '-';
            $weight = isset($rule['weight']) && is_numeric($rule['weight']) ? number_format((float) $rule['weight'], 2) : '-';
            $passedBadge = isset($rule['passed']) ? ($rule['passed'] ? "<span style='color:#16a34a;font-weight:600;'>Lolos</span>" : "<span style='color:#dc2626;font-weight:600;'>Gagal</span>") : '-';

            $rulesHtml .= "<tr>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'><strong>{$ruleCode}</strong>: {$ruleName}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$weight}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$score}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$passedBadge}</td>
            </tr>";
        }

        $failedRulesHtml = '';
        if (! empty($data['failed_rules'])) {
            $failedItems = '';
            foreach ($data['failed_rules'] as $f) {
                $fCode = htmlspecialchars((string) ($f['code'] ?? '-'));
                $fMessages = is_array($f['failures'] ?? null)
                    ? htmlspecialchars(implode('; ', $f['failures']))
                    : htmlspecialchars((string) ($f['message'] ?? 'Tidak memenuhi syarat ambang batas'));
                $failedItems .= "<li style='margin-bottom:4px;'><strong>{$fCode}</strong>: {$fMessages}</li>";
            }
            $failedRulesHtml = "
            <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:24px;'>
                <strong style='color:#991b1b;font-size:14px;'>Catatan Syarat Perlu / Ambang Batas Belum Terpenuhi:</strong>
                <ul style='margin:8px 0 0 18px;padding:0;color:#b91c1c;font-size:13px;'>{$failedItems}</ul>
            </div>";
        }

        $institution = htmlspecialchars((string) ($data['institution_name'] ?? 'Perguruan Tinggi'));
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars((string) $data['study_program_name']) . "</h3>" : '';
        $finalScore = isset($data['final_score']) && is_numeric($data['final_score']) ? number_format((float) $data['final_score'], 2) : '0.00';
        $predikat = strtoupper((string) ($data['qualification_status'] ?? 'BELUM DIHITUNG'));
        $validityYears = (int) ($data['validity_years'] ?? 5);

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Simulasi Skor - {$accreditation->title}</title>
            <style>
                body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
                .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print { body { background: #fff; padding: 0; } .container { box-shadow: none; padding: 0; max-width: 100%; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:900px;margin:0 auto 16px auto;text-align:right;'>
                <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;'>Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:20px;margin-bottom:24px;'>
                    <h1 style='margin:0 0 6px 0;font-size:22px;color:#0f172a;'>MATRIKS SIMULASI SKOR AKREDITASI</h1>
                    <h2 style='margin:0 0 6px 0;font-size:18px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:13px;'>Versi: {$data['version_label']} | Kode: {$data['accreditation_code']}</p>
                </div>

                <div style='display:flex;gap:16px;margin-bottom:28px;'>
                    <div style='flex:1;background:#f0fdf4;border:1px solid #bbf7d0;padding:20px;border-radius:8px;text-align:center;'>
                        <span style='font-size:12px;color:#166534;text-transform:uppercase;font-weight:600;'>Estimasi Skor Akhir</span>
                        <div style='font-size:36px;font-weight:bold;color:#15803d;margin-top:4px;'>{$finalScore}</div>
                        <small style='color:#166534;'>Skala 1.00 - 400.00</small>
                    </div>
                    <div style='flex:1;background:#eff6ff;border:1px solid #bfdbfe;padding:20px;border-radius:8px;text-align:center;'>
                        <span style='font-size:12px;color:#1e40af;text-transform:uppercase;font-weight:600;'>Prediksi Kualifikasi</span>
                        <div style='font-size:30px;font-weight:bold;color:#1d4ed8;margin-top:4px;'>{$predikat}</div>
                        <small style='color:#1e40af;'>Masa Berlaku: {$validityYears} Tahun</small>
                    </div>
                </div>

                {$failedRulesHtml}

                <h3 style='font-size:16px;color:#0f172a;margin-bottom:8px;'>Rincian Aturan Penilaian & Syarat Perlu</h3>
                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#f1f5f9;'>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Elemen / Aturan</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Bobot</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Nilai</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Status</th>
                        </tr>
                    </thead>
                    <tbody>{$rulesHtml}</tbody>
                </table>

                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:12px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh Antigravity i-QMS pada {$data['generated_at']}.
                </div>
            </div>
        </body>
        </html>";
    }

    public function exportEvidenceMatrixHtml(Accreditation $accreditation): string
    {
        $adapter = $this->resolveAdapter($accreditation);
        $data = $adapter->buildEvidenceMatrixData($accreditation);

        $rowsHtml = '';
        foreach ($data['rows'] as $row) {
            $linkHtml = $row['external_url'] !== '-' ? "<a href='{$row['external_url']}' target='_blank' style='color:#0284c7;'>Buka Link</a>" : '-';
            $statusBadge = $row['verification_status'] === 'Terverifikasi' ? "<span style='color:#16a34a;background:#dcfce7;padding:2px 6px;border-radius:4px;'>Terverifikasi</span>" : "<span style='color:#ca8a04;background:#fef9c3;padding:2px 6px;border-radius:4px;'>Draft</span>";
            $rowsHtml .= "<tr>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'><strong>{$row['response_key']}</strong></td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>[{$row['evidence_code']}] {$row['evidence_title']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$row['citation_page']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$row['citation_note']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$row['is_required']}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$statusBadge}</td>
                <td style='padding:8px;border:1px solid #e2e8f0;font-size:12px;'>{$linkHtml} ({$row['storage_provider']})</td>
            </tr>";
        }

        $institution = htmlspecialchars($data['institution_name']);
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars($data['study_program_name']) . "</h3>" : '';

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Peta Kesiapan Evidence - {$accreditation->title}</title>
            <style>
                body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
                .container { max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print { body { background: #fff; padding: 0; } .container { box-shadow: none; padding: 0; max-width: 100%; } .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:1100px;margin:0 auto 16px auto;text-align:right;'>
                <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600;'>Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:20px;margin-bottom:24px;'>
                    <h1 style='margin:0 0 6px 0;font-size:22px;color:#0f172a;'>PETA KESIAPAN BUKTI PENDUKUNG (EVIDENCE MATRIX)</h1>
                    <h2 style='margin:0 0 6px 0;font-size:18px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:13px;'>Total Tautan Bukti: {$data['total_evidence_links']} | Terverifikasi: {$data['verified_count']}</p>
                </div>
                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#f1f5f9;'>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Butir LED</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Dokumen Bukti</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Halaman/Bab</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Catatan Sitasi</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Kewajiban</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Status</th>
                            <th style='padding:8px;border:1px solid #cbd5e1;font-size:12px;text-align:left;'>Tautan Cloud</th>
                        </tr>
                    </thead>
                    <tbody>{$rowsHtml}</tbody>
                </table>
                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:12px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh Antigravity i-QMS pada {$data['generated_at']}.
                </div>
            </div>
        </body>
        </html>";
    }
}
