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
        $tocItems = '';
        foreach ($data['sections'] as $sIndex => $section) {
            $secId = 'sec-' . ($sIndex + 1);
            $tocItems .= "<li><a href='#{$secId}' style='color:#0284c7;text-decoration:none;'><strong>{$section['code']}</strong>: {$section['title']}</a></li>";

            $elementsHtml = '';
            foreach ($section['elements'] as $element) {
                $evidencesList = '';
                if (! empty($element['evidences'])) {
                    $evListItems = '';
                    foreach ($element['evidences'] as $ev) {
                        $citation = $ev['citation_page'] ? " (Hal/Bab: {$ev['citation_page']})" : '';
                        $linkHtml = $ev['url'] ? " — <a href='{$ev['url']}' target='_blank' style='color:#0284c7;font-weight:600;'>[Buka Link Cloud]</a>" : '';
                        $statusColor = ($ev['status'] ?? '') === 'verified' ? '#16a34a' : '#d97706';
                        $statusBg = ($ev['status'] ?? '') === 'verified' ? '#dcfce7' : '#fef3c7';

                        $evListItems .= "<li style='margin-bottom:6px;'>
                            <strong>[{$ev['code']}] {$ev['title']}</strong>{$citation}
                            <span style='font-size:10px;background:{$statusBg};color:{$statusColor};padding:2px 6px;border-radius:4px;font-weight:600;margin-left:4px;'>" . strtoupper((string) ($ev['status'] ?? 'draft')) . "</span>
                            " . ($ev['citation_note'] ? "<div style='font-size:11px;color:#64748b;font-style:italic;'>Catatan: \"{$ev['citation_note']}\"</div>" : '') . "
                            {$linkHtml}
                        </li>";
                    }
                    $evidencesList = "
                    <div style='margin-top:12px;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;'>
                        <strong style='font-size:11px;text-transform:uppercase;color:#475569;letter-spacing:0.05em;'>📎 Dokumen Bukti Tertaut:</strong>
                        <ul style='margin:8px 0 0 16px;padding:0;font-size:12px;'>{$evListItems}</ul>
                    </div>";
                }

                $elementsHtml .= "
                <div style='margin-bottom:24px;padding:18px;background:#ffffff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 1px 2px rgba(0,0,0,0.03);'>
                    <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;border-bottom:1px solid #f1f5f9;padding-bottom:8px;'>
                        <h4 style='margin:0;color:#0f172a;font-size:14px;font-weight:700;'>[{$element['code']}] {$element['title']}</h4>
                        <div style='display:flex;gap:6px;align-items:center;'>
                            <span style='font-size:11px;background:#f1f5f9;color:#475569;padding:3px 8px;border-radius:4px;font-weight:600;'>Bobot: " . number_format((float) $element['weight'], 2) . "</span>
                            <span style='font-size:11px;background:#e0f2fe;color:#0369a1;padding:3px 8px;border-radius:4px;font-weight:700;'>" . strtoupper((string) $element['status']) . "</span>
                        </div>
                    </div>
                    <div style='font-size:13px;color:#334155;line-height:1.7;white-space:pre-wrap;background:#fafafa;padding:14px;border-radius:6px;border:1px solid #f1f5f9;font-family:inherit;'>" . ($element['response_text'] ?: '<em style="color:#94a3b8;">Belum ada narasi evaluasi diri yang diisi.</em>') . "</div>
                    {$evidencesList}
                </div>";
            }

            $sectionsHtml .= "
            <div id='{$secId}' style='margin-bottom:40px;page-break-inside:avoid;'>
                <div style='border-bottom:2px solid #0284c7;padding-bottom:8px;margin-bottom:18px;'>
                    <h3 style='margin:0;color:#0f2d6e;font-size:17px;font-weight:700;'>{$section['code']}: {$section['title']}</h3>
                    " . ($section['description'] ? "<p style='margin:4px 0 0 0;color:#64748b;font-size:12px;font-style:italic;'>{$section['description']}</p>" : '') . "
                </div>
                {$elementsHtml}
            </div>";
        }

        $institution = htmlspecialchars((string) ($data['institution_name'] ?? 'Perguruan Tinggi'));
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:6px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi " . htmlspecialchars((string) $data['study_program_name']) . "</h3>" : '';
        $title = htmlspecialchars((string) ($data['title'] ?? 'Laporan Evaluasi Diri'));

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>LED — {$title}</title>
            <style>
                @page { size: A4 portrait; margin: 20mm 15mm 20mm 20mm; }
                body { font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; font-size: 13px; line-height: 1.5; }
                .container { max-width: 960px; margin: 0 auto; background: #ffffff; padding: 48px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                .cover-box { text-align: center; border: 2px double #cbd5e1; padding: 40px 24px; border-radius: 8px; margin-bottom: 36px; background: #fcfdfe; }
                @media print {
                    body { background: #fff; padding: 0; }
                    .container { box-shadow: none; padding: 0; max-width: 100%; }
                    .no-print { display: none !important; }
                    .page-break { page-break-after: always; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:960px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#e0f2fe;padding:12px 18px;border-radius:8px;border:1px solid #bae6fd;'>
                <span style='color:#0369a1;font-weight:600;'>📄 Pratinjau Dokumen LED Siap Cetak (A4 Standard)</span>
                <div style='display:flex;gap:8px;'>
                    <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;font-size:12px;'>🖨️ Cetak / Simpan PDF</button>
                </div>
            </div>

            <div class='container'>
                {{-- Cover Box --}}
                <div class='cover-box'>
                    <div style='font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;'>{$data['type']}</div>
                    <h1 style='margin:0 0 8px 0;font-size:24px;color:#0f2d6e;font-weight:800;letter-spacing:-0.02em;'>LAPORAN EVALUASI DIRI (LED)</h1>
                    <h2 style='margin:0 0 6px 0;font-size:17px;color:#1e3a8a;font-weight:700;'>{$institution}</h2>
                    {$prodi}
                    <div style='width:60px;height:3px;background:#0284c7;margin:18px auto;'></div>
                    <p style='margin:0;color:#475569;font-size:12px;'>Instrumen: <strong>{$data['version_label']}</strong> | Kode Registrasi: <strong>{$data['accreditation_code']}</strong></p>
                    <p style='margin:4px 0 0 0;color:#94a3b8;font-size:11px;'>Tanggal Cetak: {$data['generated_at']}</p>
                </div>

                {{-- Table of Contents --}}
                <div style='background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin-bottom:36px;'>
                    <h3 style='margin:0 0 12px 0;font-size:14px;color:#0f2d6e;text-transform:uppercase;letter-spacing:0.05em;'>Daftar Kriteria & Bab</h3>
                    <ul style='margin:0;padding-left:20px;line-height:1.8;font-size:13px;'>
                        {$tocItems}
                    </ul>
                </div>

                <div class='page-break'></div>

                {{-- Document Body --}}
                {$sectionsHtml}

                <div style='border-top:1px solid #e2e8f0;padding-top:20px;margin-top:40px;font-size:11px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh sistem penjaminan mutu <strong>Antigravity i-QMS</strong> pada {$data['generated_at']}.
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
                $headersHtml .= "<th style='background:#0f2d6e;color:#ffffff;padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:center;'>{$header}</th>";
            }

            $rowsHtml = '';
            foreach ($table['rows'] as $rIdx => $row) {
                $cellsHtml = '';
                $isAlt = $rIdx % 2 !== 0;
                foreach ($row as $cell) {
                    $cellsHtml .= "<td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;color:#1e293b;background:" . ($isAlt ? '#f8fafc' : '#ffffff') . ";'>{$cell}</td>";
                }
                $rowsHtml .= "<tr>{$cellsHtml}</tr>";
            }

            $tablesHtml .= "
            <div style='margin-bottom:36px;page-break-inside:avoid;'>
                <div style='background:#f1f5f9;border-left:4px solid #0284c7;padding:10px 14px;border-radius:0 6px 6px 0;margin-bottom:12px;'>
                    <h3 style='margin:0;font-size:14px;font-weight:700;color:#0f172a;'>{$table['code']}: {$table['title']}</h3>
                    " . ($table['description'] ? "<p style='margin:3px 0 0 0;font-size:11px;color:#64748b;'>{$table['description']}</p>" : '') . "
                </div>
                <div style='overflow-x:auto;'>
                    <table style='width:100%;border-collapse:collapse;margin-bottom:12px;'>
                        <thead><tr>{$headersHtml}</tr></thead>
                        <tbody>{$rowsHtml}</tbody>
                    </table>
                </div>
            </div>";
        }

        $institution = htmlspecialchars((string) ($data['institution_name'] ?? 'Perguruan Tinggi'));
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars((string) $data['study_program_name']) . "</h3>" : '';

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>LKPS / LKPT — {$accreditation->title}</title>
            <style>
                @page { size: A4 landscape; margin: 15mm; }
                body { font-family: 'Segoe UI', -apple-system, Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; font-size: 12px; }
                .container { max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print {
                    body { background: #fff; padding: 0; }
                    .container { box-shadow: none; padding: 0; max-width: 100%; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:1100px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#f3e8ff;padding:12px 18px;border-radius:8px;border:1px solid #e9d5ff;'>
                <span style='color:#7e22ce;font-weight:600;'>📊 Tabel Borang LKPS / LKPT Siap Cetak (A4 Landscape)</span>
                <button onclick='window.print()' style='background:#9333ea;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;'>🖨️ Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:18px;margin-bottom:28px;'>
                    <h1 style='margin:0 0 6px 0;font-size:22px;color:#0f172a;font-weight:800;'>LAPORAN KINERJA (LKPS / LKPT)</h1>
                    <h2 style='margin:0 0 4px 0;font-size:16px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:12px;'>Tipe: {$data['type']} | Kode: {$data['accreditation_code']}</p>
                </div>
                {$tablesHtml}
                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:11px;color:#94a3b8;text-align:center;'>
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
            $passedBadge = isset($rule['passed'])
                ? ($rule['passed'] ? "<span style='color:#15803d;background:#dcfce7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:11px;'>✓ LOLOS</span>" : "<span style='color:#b91c1c;background:#fee2e2;padding:2px 8px;border-radius:4px;font-weight:700;font-size:11px;'>✕ GAGAL</span>")
                : '-';

            $rulesHtml .= "<tr>
                <td style='padding:10px 12px;border:1px solid #e2e8f0;font-size:12px;'><strong>{$ruleCode}</strong>: {$ruleName}</td>
                <td style='padding:10px 12px;border:1px solid #e2e8f0;font-size:12px;text-align:right;'>{$weight}</td>
                <td style='padding:10px 12px;border:1px solid #e2e8f0;font-size:12px;text-align:right;font-weight:600;'>{$score}</td>
                <td style='padding:10px 12px;border:1px solid #e2e8f0;font-size:12px;text-align:center;'>{$passedBadge}</td>
            </tr>";
        }

        $failedRulesHtml = '';
        if (! empty($data['failed_rules'])) {
            $failedItems = '';
            foreach ($data['failed_rules'] as $f) {
                $fCode = htmlspecialchars((string) ($f['code'] ?? '-'));
                $fMessages = is_array($f['failures'] ?? null)
                    ? htmlspecialchars(implode('; ', $f['failures']))
                    : htmlspecialchars((string) ($f['message'] ?? 'Tidak memenuhi syarat ambang batas kriteria'));
                $failedItems .= "<li style='margin-bottom:6px;'><strong>[{$fCode}]</strong> {$fMessages}</li>";
            }
            $failedRulesHtml = "
            <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px 20px;margin-bottom:28px;'>
                <div style='display:flex;align-items:center;gap:8px;margin-bottom:8px;'>
                    <span style='font-size:18px;'>⚠️</span>
                    <strong style='color:#991b1b;font-size:14px;'>Catatan Syarat Perlu / Ambang Batas Belum Terpenuhi:</strong>
                </div>
                <ul style='margin:0 0 0 20px;padding:0;color:#b91c1c;font-size:12px;line-height:1.5;'>{$failedItems}</ul>
            </div>";
        } else {
            $failedRulesHtml = "
            <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 18px;margin-bottom:28px;display:flex;align-items:center;gap:10px;'>
                <span style='font-size:20px;'>✅</span>
                <span style='font-size:13px;color:#166534;font-weight:600;'>Seluruh Syarat Perlu Akreditasi & Syarat Perlu Peringkat telah terpenuhi.</span>
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
            <title>Simulasi Skor & Syarat Perlu — {$accreditation->title}</title>
            <style>
                @page { size: A4 portrait; margin: 20mm; }
                body { font-family: 'Segoe UI', -apple-system, Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; font-size: 13px; line-height: 1.5; }
                .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print {
                    body { background: #fff; padding: 0; }
                    .container { box-shadow: none; padding: 0; max-width: 100%; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:900px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#ecfdf5;padding:12px 18px;border-radius:8px;border:1px solid #a7f3d0;'>
                <span style='color:#065f46;font-weight:600;'>🏆 Dokumen Simulasi Skor & Syarat Perlu Akreditasi</span>
                <button onclick='window.print()' style='background:#059669;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;'>🖨️ Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:20px;margin-bottom:24px;'>
                    <h1 style='margin:0 0 6px 0;font-size:20px;color:#0f172a;font-weight:800;'>MATRIKS SIMULASI SKOR AKREDITASI & SYARAT PERLU</h1>
                    <h2 style='margin:0 0 6px 0;font-size:16px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:12px;'>Instrumen: {$data['version_label']} | Kode: {$data['accreditation_code']}</p>
                </div>

                {{-- Executive Summary Cards --}}
                <div style='display:flex;gap:16px;margin-bottom:28px;'>
                    <div style='flex:1;background:#f0fdf4;border:1px solid #bbf7d0;padding:22px;border-radius:10px;text-align:center;'>
                        <span style='font-size:11px;color:#166534;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;'>Estimasi Skor Akhir</span>
                        <div style='font-size:38px;font-weight:800;color:#15803d;margin-top:4px;'>{$finalScore}</div>
                        <small style='color:#166534;'>Skala Penilaian: 1.00 — 400.00</small>
                    </div>
                    <div style='flex:1;background:#eff6ff;border:1px solid #bfdbfe;padding:22px;border-radius:10px;text-align:center;'>
                        <span style='font-size:11px;color:#1e40af;text-transform:uppercase;font-weight:700;letter-spacing:0.05em;'>Prediksi Peringkat</span>
                        <div style='font-size:30px;font-weight:800;color:#1d4ed8;margin-top:4px;'>{$predikat}</div>
                        <small style='color:#1e40af;'>Masa Berlaku: {$validityYears} Tahun</small>
                    </div>
                </div>

                {{-- Syarat Perlu / Threshold Analysis --}}
                {$failedRulesHtml}

                {{-- Scoring Rules Detail Table --}}
                <h3 style='font-size:15px;color:#0f172a;font-weight:700;margin-bottom:10px;'>Rincian Nilai Indikator & Syarat Perlu</h3>
                <table style='width:100%;border-collapse:collapse;margin-bottom:32px;'>
                    <thead>
                        <tr style='background:#f1f5f9;'>
                            <th style='padding:10px 12px;border:1px solid #cbd5e1;font-size:11px;text-align:left;'>Elemen / Aturan Kriteria</th>
                            <th style='padding:10px 12px;border:1px solid #cbd5e1;font-size:11px;text-align:right;width:80px;'>Bobot</th>
                            <th style='padding:10px 12px;border:1px solid #cbd5e1;font-size:11px;text-align:right;width:80px;'>Nilai</th>
                            <th style='padding:10px 12px;border:1px solid #cbd5e1;font-size:11px;text-align:center;width:100px;'>Status</th>
                        </tr>
                    </thead>
                    <tbody>{$rulesHtml}</tbody>
                </table>

                {{-- Sign-off Block --}}
                <table style='width:100%;margin-top:40px;'>
                    <tr>
                        <td style='width:50%;text-align:center;'>
                            Disusun oleh,<br><strong>Ketua Tim Akreditasi</strong><br><br><br><br>
                            ( .................................................... )
                        </td>
                        <td style='width:50%;text-align:center;'>
                            Disetujui oleh,<br><strong>Pimpinan UPPS / Dekan</strong><br><br><br><br>
                            ( .................................................... )
                        </td>
                    </tr>
                </table>

                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:11px;color:#94a3b8;text-align:center;'>
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
        foreach ($data['rows'] as $rIdx => $row) {
            $linkHtml = $row['external_url'] !== '-' ? "<a href='{$row['external_url']}' target='_blank' style='color:#0284c7;font-weight:600;'>Buka Link</a>" : '-';
            $statusBadge = $row['verification_status'] === 'Terverifikasi'
                ? "<span style='color:#16a34a;background:#dcfce7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>TERVERIFIKASI</span>"
                : "<span style='color:#ca8a04;background:#fef9c3;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>DRAFT</span>";

            $isAlt = $rIdx % 2 !== 0;

            $rowsHtml .= "<tr style='background:" . ($isAlt ? '#f8fafc' : '#ffffff') . ";'>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;'><strong>{$row['response_key']}</strong></td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;'><strong>[{$row['evidence_code']}]</strong> {$row['evidence_title']}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;text-align:center;'>{$row['citation_page']}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;font-style:italic;'>{$row['citation_note']}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;text-align:center;'>{$row['is_required']}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;text-align:center;'>{$statusBadge}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;'>{$linkHtml} ({$row['storage_provider']})</td>
            </tr>";
        }

        $institution = htmlspecialchars((string) ($data['institution_name'] ?? 'Perguruan Tinggi'));
        $prodi = isset($data['study_program_name']) ? "<h3 style='margin:4px 0 0 0;font-size:16px;color:#0284c7;'>Program Studi: " . htmlspecialchars((string) $data['study_program_name']) . "</h3>" : '';

        return "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Peta Bukti Pendukung — {$accreditation->title}</title>
            <style>
                @page { size: A4 landscape; margin: 15mm; }
                body { font-family: 'Segoe UI', -apple-system, Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; margin: 0; padding: 24px; font-size: 12px; }
                .container { max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
                @media print {
                    body { background: #fff; padding: 0; }
                    .container { box-shadow: none; padding: 0; max-width: 100%; }
                    .no-print { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class='no-print' style='max-width:1100px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#f0f9ff;padding:12px 18px;border-radius:8px;border:1px solid #bae6fd;'>
                <span style='color:#0369a1;font-weight:600;'>📎 Matriks Peta Bukti & Sitasi Dokumen Akreditasi</span>
                <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;'>🖨️ Cetak / Simpan PDF</button>
            </div>
            <div class='container'>
                <div style='text-align:center;border-bottom:3px double #cbd5e1;padding-bottom:18px;margin-bottom:24px;'>
                    <h1 style='margin:0 0 6px 0;font-size:20px;color:#0f172a;font-weight:800;'>PETA KESIAPAN BUKTI PENDUKUNG (EVIDENCE MATRIX)</h1>
                    <h2 style='margin:0 0 4px 0;font-size:16px;color:#334155;'>{$institution}</h2>
                    {$prodi}
                    <p style='margin:8px 0 0 0;color:#64748b;font-size:12px;'>Total Tautan Bukti: <strong>{$data['total_evidence_links']}</strong> | Terverifikasi: <strong>{$data['verified_count']}</strong></p>
                </div>
                <table style='width:100%;border-collapse:collapse;margin-bottom:24px;'>
                    <thead>
                        <tr style='background:#0f2d6e;color:#ffffff;'>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:left;'>Butir LED</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:left;'>Dokumen Bukti</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:center;width:70px;'>Hal/Bab</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:left;'>Catatan Sitasi</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:center;width:70px;'>Wajib</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:center;width:100px;'>Status</th>
                            <th style='padding:8px 10px;border:1px solid #cbd5e1;font-size:11px;text-align:left;'>Tautan Cloud</th>
                        </tr>
                    </thead>
                    <tbody>{$rowsHtml}</tbody>
                </table>
                <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:11px;color:#94a3b8;text-align:center;'>
                    Dokumen ini digenerate secara otomatis oleh Antigravity i-QMS pada {$data['generated_at']}.
                </div>
            </div>
        </body>
        </html>";
    }
}
