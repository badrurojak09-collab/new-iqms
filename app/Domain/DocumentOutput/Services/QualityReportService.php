<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Services;

use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\RtmParticipant;

final class QualityReportService
{
    public function exportRtmMinutesHtml(RtmMeeting $meeting): string
    {
        $meeting->loadMissing([
            'perguruanTinggi',
            'programStudi',
            'chair',
            'participants.user',
            'decisions.finding',
        ]);

        $ptName = htmlspecialchars((string) ($meeting->perguruanTinggi?->nama_pt ?? 'Institusi'));
        $prodiName = $meeting->programStudi ? ' — Program Studi ' . htmlspecialchars((string) $meeting->programStudi->nama_prodi) : '';
        $title = htmlspecialchars((string) $meeting->title);
        $code = htmlspecialchars((string) $meeting->code);
        $date = $meeting->held_at?->translatedFormat('l, d F Y') ?? '-';
        $chair = htmlspecialchars((string) ($meeting->chair?->name ?? 'Pimpinan RTM'));
        $minutesNotes = nl2br(htmlspecialchars((string) ($meeting->minutes ?? '')));

        $participantsRows = '';
        $no = 1;
        foreach ($meeting->participants as $p) {
            $name = htmlspecialchars((string) ($p->user?->name ?? '-'));
            $role = htmlspecialchars((string) ($p->role ?? 'Peserta'));
            $attendanceBadge = $p->attended
                ? "<span style='color:#15803d;background:#dcfce7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>HADIR</span>"
                : "<span style='color:#b91c1c;background:#fee2e2;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>TIDAK HADIR</span>";

            $isAlt = $no % 2 === 0;
            $participantsRows .= "<tr style='background:" . ($isAlt ? '#f8fafc' : '#ffffff') . ";'>
                <td style='text-align:center;padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;'>{$no}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;font-weight:600;'>{$name}</td>
                <td style='padding:8px 10px;border:1px solid #e2e8f0;font-size:11px;'>{$role}</td>
                <td style='text-align:center;padding:8px 10px;border:1px solid #e2e8f0;'>{$attendanceBadge}</td>
            </tr>";
            $no++;
        }

        $decisionsRows = '';
        $decNo = 1;
        foreach ($meeting->decisions as $d) {
            $decCode = htmlspecialchars((string) ($d->code ?? "DEC-{$decNo}"));
            $decText = nl2br(htmlspecialchars((string) ($d->decision ?? '')));
            $rationale = htmlspecialchars((string) ($d->rationale ?? '-'));
            $statusBadge = match ($d->status) {
                'closed', 'completed' => "<span style='color:#15803d;background:#dcfce7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>SELESAI</span>",
                'in_progress' => "<span style='color:#0369a1;background:#e0f2fe;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>PROSES</span>",
                default => "<span style='color:#b45309;background:#fef3c7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>TERBUKA</span>",
            };

            $isAlt = $decNo % 2 === 0;
            $decisionsRows .= "<tr style='background:" . ($isAlt ? '#f8fafc' : '#ffffff') . ";'>
                <td style='text-align:center;padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;'>{$decNo}</td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;'><strong>{$decCode}</strong></td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;line-height:1.5;'>{$decText}</td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;color:#64748b;'>{$rationale}</td>
                <td style='text-align:center;padding:10px;border:1px solid #e2e8f0;vertical-align:top;'>{$statusBadge}</td>
            </tr>";
            $decNo++;
        }

        return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='utf-8'>
    <title>Risalah RTM — {$title}</title>
    <style>
        @page { size: A4 portrait; margin: 20mm; }
        body { font-family: 'Segoe UI', -apple-system, Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; color: #1e293b; font-size: 12px; line-height: 1.5; background: #f8fafc; }
        .container { max-width: 900px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .header { text-align: center; border-bottom: 2px solid #0f2d6e; padding-bottom: 15px; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f2d6e; font-weight: 800; text-transform: uppercase; }
        .header h2 { margin: 4px 0 0 0; font-size: 14px; font-weight: 600; color: #475569; }
        .meta-table { width: 100%; margin-bottom: 24px; border-collapse: collapse; }
        .meta-table td { padding: 4px 8px; vertical-align: top; font-size: 12px; }
        .meta-table td.label { width: 160px; font-weight: 600; color: #475569; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 24px; }
        table.data-table th { background: #0f2d6e; color: #ffffff; font-weight: 700; font-size: 11px; padding: 8px 10px; border: 1px solid #cbd5e1; text-align: left; }
        .section-title { font-size: 13px; font-weight: 700; color: #0f2d6e; margin-top: 24px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-transform: uppercase; letter-spacing: 0.05em; }
        @media print {
            body { background: #fff; padding: 0; }
            .container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class='no-print' style='max-width:900px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#e0f2fe;padding:12px 18px;border-radius:8px;border:1px solid #bae6fd;'>
        <span style='color:#0369a1;font-weight:600;'>📋 Dokumen Risalah Rapat Tinjauan Manajemen (RTM) Siap Cetak</span>
        <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;'>🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class='container'>
        <div class='header'>
            <h1>{$ptName}</h1>
            <h2>RISALAH RAPAT TINJAUAN MANAJEMEN (RTM){$prodiName}</h2>
        </div>

        <table class='meta-table'>
            <tr><td class='label'>Kode Dokumen</td><td>: <strong>{$code}</strong></td></tr>
            <tr><td class='label'>Agenda Pembahasan</td><td>: <strong>{$title}</strong></td></tr>
            <tr><td class='label'>Hari & Tanggal</td><td>: {$date}</td></tr>
            <tr><td class='label'>Pimpinan Rapat</td><td>: {$chair}</td></tr>
        </table>

        " . ($minutesNotes ? "<div class='section-title'>I. Catatan & Ringkasan Pembahasan RTM</div><div style='padding:14px;background:#fafafa;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:24px;line-height:1.6;font-size:12px;'>{$minutesNotes}</div>" : '') . "

        <div class='section-title'>II. Daftar Kehadiran Peserta RTM</div>
        <table class='data-table'>
            <thead>
                <tr>
                    <th style='width: 35px; text-align:center;'>No</th>
                    <th>Nama Peserta</th>
                    <th style='width: 180px;'>Jabatan / Unit Kerja</th>
                    <th style='width: 100px; text-align:center;'>Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                " . ($participantsRows ?: "<tr><td colspan='4' style='text-align:center; color:#94a3b8; padding:16px;'>Tidak ada data peserta</td></tr>") . "
            </tbody>
        </table>

        <div class='section-title'>III. Rekapitulasi Keputusan RTM & Rencana Tindak Lanjut (RTL)</div>
        <table class='data-table'>
            <thead>
                <tr>
                    <th style='width: 35px; text-align:center;'>No</th>
                    <th style='width: 110px;'>Kode</th>
                    <th>Butir Keputusan & Arahan</th>
                    <th>Dasar Pertimbangan (Rationale)</th>
                    <th style='width: 90px; text-align:center;'>Status</th>
                </tr>
            </thead>
            <tbody>
                " . ($decisionsRows ?: "<tr><td colspan='5' style='text-align:center; color:#94a3b8; padding:16px;'>Belum ada butir keputusan yang dicatat.</td></tr>") . "
            </tbody>
        </table>

        <table style='width: 100%; margin-top: 48px; page-break-inside: avoid;'>
            <tr>
                <td style='width: 50%; text-align: center;'>
                    Notulis / Sekretaris RTM,<br><br><br><br><br>
                    ( .................................................... )
                </td>
                <td style='width: 50%; text-align: center;'>
                    Disahkan oleh,<br><strong>Pimpinan RTM / Ketua Penjaminan Mutu</strong><br><br><br><br>
                    <u><strong>{$chair}</strong></u>
                </td>
            </tr>
        </table>

        <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:11px;color:#94a3b8;text-align:center;'>
            Dokumen resmi ini digenerate secara otomatis oleh <strong>Antigravity i-QMS</strong>.
        </div>
    </div>
</body>
</html>";
    }

    public function exportAmiSummaryHtml(AmiCycle $cycle): string
    {
        $cycle->loadMissing([
            'perguruanTinggi',
            'programStudi',
            'findings.evidenceLinks.evidence',
            'findings.reporter',
        ]);

        $ptName = htmlspecialchars((string) ($cycle->perguruanTinggi?->nama_pt ?? 'Institusi'));
        $prodiName = $cycle->programStudi ? 'Program Studi: ' . htmlspecialchars((string) $cycle->programStudi->nama_prodi) : 'Tingkat Institusi / Fakultas';
        $cycleName = htmlspecialchars((string) $cycle->name);
        $code = htmlspecialchars((string) $cycle->code);
        $period = (string) ($cycle->period_year ?? '-');

        // Calculate finding metrics
        $findings = $cycle->findings;
        $totalFindings = $findings->count();
        $ktsMayorCount = $findings->where('classification', 'nonconformity')->where('severity', 'major')->count();
        $ktsMinorCount = $findings->where('classification', 'nonconformity')->where('severity', '!=', 'major')->count();
        $ptpCount = $findings->where('classification', 'opportunity')->count();
        $obCount = $findings->whereNotIn('classification', ['nonconformity', 'opportunity'])->count();

        $findingsRows = '';
        $no = 1;
        foreach ($findings as $f) {
            $fCode = htmlspecialchars((string) $f->code);
            $fClass = match ($f->classification) {
                'nonconformity' => "<span style='color:#b91c1c;background:#fee2e2;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>KTS (" . strtoupper((string) $f->severity) . ")</span>",
                'opportunity' => "<span style='color:#0369a1;background:#e0f2fe;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>PELUANG PERBAIKAN</span>",
                default => "<span style='color:#b45309;background:#fef3c7;padding:2px 8px;border-radius:4px;font-weight:700;font-size:10px;'>OBSERVASI</span>",
            };

            $cond = nl2br(htmlspecialchars((string) $f->condition));
            $cause = htmlspecialchars((string) ($f->cause ?? '-'));
            $rec = nl2br(htmlspecialchars((string) ($f->recommendation ?? '-')));

            $evItems = '';
            foreach ($f->evidenceLinks as $link) {
                $evTitle = htmlspecialchars((string) ($link->evidence?->title ?? 'Dokumen Bukti'));
                $citation = $link->citation_page ? " (Hal: {$link->citation_page})" : '';
                $evItems .= "<li style='margin-top:2px;'>[{$link->evidence?->code}] {$evTitle}{$citation}</li>";
            }
            $evList = $evItems ? "<div style='margin-top:6px;font-size:10px;color:#475569;background:#f1f5f9;padding:6px;border-radius:4px;'><strong>Bukti Objektif:</strong><ul style='margin:2px 0 0 14px;padding:0;'>{$evItems}</ul></div>" : '';

            $isAlt = $no % 2 === 0;
            $findingsRows .= "
            <tr style='background:" . ($isAlt ? '#f8fafc' : '#ffffff') . ";'>
                <td style='text-align:center;padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;'>{$no}</td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;'>
                    <strong style='font-family:monospace;font-size:12px;'>{$fCode}</strong><br>
                    <div style='margin-top:4px;'>{$fClass}</div>
                </td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;line-height:1.5;'>{$cond}{$evList}</td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;color:#475569;'>{$cause}</td>
                <td style='padding:10px;border:1px solid #e2e8f0;font-size:11px;vertical-align:top;line-height:1.5;color:#0f2d6e;'>{$rec}</td>
            </tr>";
            $no++;
        }

        return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='utf-8'>
    <title>Laporan Hasil AMI — {$code}</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Segoe UI', -apple-system, Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; color: #1e293b; font-size: 12px; line-height: 1.5; background: #f8fafc; }
        .container { max-width: 1100px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .header { text-align: center; border-bottom: 2px solid #0f2d6e; padding-bottom: 15px; margin-bottom: 24px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f2d6e; font-weight: 800; text-transform: uppercase; }
        .header h2 { margin: 4px 0 0 0; font-size: 14px; font-weight: 600; color: #475569; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 4px 8px; vertical-align: top; font-size: 12px; }
        .meta-table td.label { width: 160px; font-weight: 600; color: #475569; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 24px; }
        table.data-table th { background: #0f2d6e; color: #ffffff; font-weight: 700; font-size: 11px; padding: 8px 10px; border: 1px solid #cbd5e1; text-align: left; }
        @media print {
            body { background: #fff; padding: 0; }
            .container { box-shadow: none; padding: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class='no-print' style='max-width:1100px;margin:0 auto 16px auto;display:flex;justify-content:space-between;align-items:center;background:#e0f2fe;padding:12px 18px;border-radius:8px;border:1px solid #bae6fd;'>
        <span style='color:#0369a1;font-weight:600;'>📑 Laporan Ringkasan Hasil Audit Mutu Internal (AMI) Siap Cetak</span>
        <button onclick='window.print()' style='background:#0284c7;color:#fff;border:none;padding:8px 18px;border-radius:6px;cursor:pointer;font-weight:700;'>🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class='container'>
        <div class='header'>
            <h1>{$ptName}</h1>
            <h2>LAPORAN HASIL AUDIT MUTU INTERNAL (AMI)</h2>
        </div>

        <table class='meta-table'>
            <tr><td class='label'>Siklus AMI</td><td>: <strong>[{$code}] {$cycleName}</strong></td></tr>
            <tr><td class='label'>Lingkup Unit Kerja</td><td>: {$prodiName}</td></tr>
            <tr><td class='label'>Tahun Pelaksanaan</td><td>: {$period}</td></tr>
        </table>

        {{-- Statistical Finding Badges --}}
        <div style='display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;'>
            <div style='background:#fef2f2;border:1px solid #fecaca;padding:12px;border-radius:8px;text-align:center;'>
                <div style='font-size:10px;color:#991b1b;font-weight:700;text-transform:uppercase;'>KTS Mayor</div>
                <div style='font-size:24px;font-weight:800;color:#b91c1c;margin-top:2px;'>{$ktsMayorCount}</div>
            </div>
            <div style='background:#fffbeb;border:1px solid #fde68a;padding:12px;border-radius:8px;text-align:center;'>
                <div style='font-size:10px;color:#92400e;font-weight:700;text-transform:uppercase;'>KTS Minor</div>
                <div style='font-size:24px;font-weight:800;color:#d97706;margin-top:2px;'>{$ktsMinorCount}</div>
            </div>
            <div style='background:#f0f9ff;border:1px solid #bae6fd;padding:12px;border-radius:8px;text-align:center;'>
                <div style='font-size:10px;color:#075985;font-weight:700;text-transform:uppercase;'>Peluang Peningkatan</div>
                <div style='font-size:24px;font-weight:800;color:#0284c7;margin-top:2px;'>{$ptpCount}</div>
            </div>
            <div style='background:#f8fafc;border:1px solid #e2e8f0;padding:12px;border-radius:8px;text-align:center;'>
                <div style='font-size:10px;color:#475569;font-weight:700;text-transform:uppercase;'>Observasi</div>
                <div style='font-size:24px;font-weight:800;color:#334155;margin-top:2px;'>{$obCount}</div>
            </div>
        </div>

        <table class='data-table'>
            <thead>
                <tr>
                    <th style='width: 30px; text-align:center;'>No</th>
                    <th style='width: 140px;'>Kode & Klasifikasi</th>
                    <th>Kondisi Temuan & Bukti Objektif</th>
                    <th style='width: 180px;'>Akar Penyebab (Root Cause)</th>
                    <th style='width: 220px;'>Rekomendasi Tim Auditor</th>
                </tr>
            </thead>
            <tbody>
                " . ($findingsRows ?: "<tr><td colspan='5' style='text-align:center; color:#94a3b8; padding:20px;'>Tidak ada butir temuan pada siklus audit ini.</td></tr>") . "
            </tbody>
        </table>

        {{-- Sign-off Block --}}
        <table style='width:100%;margin-top:40px;page-break-inside:avoid;'>
            <tr>
                <td style='width:50%;text-align:center;'>
                    Auditee / Pimpinan Unit Kerja,<br><br><br><br><br>
                    ( .................................................... )
                </td>
                <td style='width:50%;text-align:center;'>
                    Ketua Tim Auditor AMI,<br><br><br><br><br>
                    ( .................................................... )
                </td>
            </tr>
        </table>

        <div style='border-top:1px solid #e2e8f0;padding-top:16px;margin-top:30px;font-size:11px;color:#94a3b8;text-align:center;'>
            Dokumen resmi ini digenerate secara otomatis oleh <strong>Antigravity i-QMS</strong>.
        </div>
    </div>
</body>
</html>";
    }
}
