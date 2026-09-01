<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Services;

use App\Models\AmiCycle;
use App\Models\RtlAction;
use App\Models\RtmMeeting;
use App\Models\SpmiStandard;

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

        $ptName = htmlspecialchars($meeting->perguruanTinggi?->nama_pt ?? 'Institusi');
        $prodiName = $meeting->programStudi ? ' - Program Studi ' . htmlspecialchars($meeting->programStudi->nama_prodi) : '';
        $title = htmlspecialchars($meeting->title);
        $code = htmlspecialchars($meeting->code);
        $date = $meeting->held_at?->translatedFormat('d F Y') ?? '-';
        $chair = htmlspecialchars($meeting->chair?->name ?? 'Pimpinan RTM');
        $minutesNotes = nl2br(htmlspecialchars($meeting->minutes ?? ''));

        $participantsRows = '';
        $no = 1;
        foreach ($meeting->participants as $p) {
            $name = htmlspecialchars($p->user?->name ?? '-');
            $role = htmlspecialchars($p->role ?? 'Peserta');
            $attendance = $p->attended ? 'Hadir' : 'Tidak Hadir';
            $participantsRows .= "<tr><td style='text-align:center;'>{$no}</td><td>{$name}</td><td>{$role}</td><td style='text-align:center;'>{$attendance}</td></tr>";
            $no++;
        }

        $decisionsRows = '';
        $decNo = 1;
        foreach ($meeting->decisions as $d) {
            $decCode = htmlspecialchars($d->code ?? "DEC-{$decNo}");
            $decText = nl2br(htmlspecialchars($d->decision ?? ''));
            $rationale = htmlspecialchars($d->rationale ?? '-');
            $status = htmlspecialchars($d->status ?? 'open');
            $decisionsRows .= "
            <tr>
                <td style='text-align:center;'>{$decNo}</td>
                <td><strong>{$decCode}</strong></td>
                <td>{$decText}</td>
                <td>{$rationale}</td>
                <td style='text-align:center;'>{$status}</td>
            </tr>";
            $decNo++;
        }

        return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='utf-8'>
    <title>Risalah RTM - {$title}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; color: #1e293b; font-size: 13px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f172a; text-transform: uppercase; }
        .header h2 { margin: 4px 0 0 0; font-size: 14px; font-weight: normal; color: #475569; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 4px 8px; vertical-align: top; }
        .meta-table td.label { width: 180px; font-weight: 600; color: #334155; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; vertical-align: top; }
        table.data-table th { background: #f1f5f9; font-weight: 600; color: #1e293b; }
        .section-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 20px; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
        @media print { body { margin: 15mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class='no-print' style='background: #e0f2fe; border: 1px solid #0284c7; padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;'>
        <span><strong>Dokumen Siap Cetak:</strong> Risalah Rapat Tinjauan Manajemen (RTM)</span>
        <button onclick='window.print()' style='background: #0284c7; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;'>Cetak / Simpan PDF</button>
    </div>

    <div class='header'>
        <h1>{$ptName}</h1>
        <h2>RISALAH RAPAT TINJAUAN MANAJEMEN (RTM){$prodiName}</h2>
    </div>

    <table class='meta-table'>
        <tr><td class='label'>Kode Rapat</td><td>: <strong>{$code}</strong></td></tr>
        <tr><td class='label'>Agenda / Topik</td><td>: <strong>{$title}</strong></td></tr>
        <tr><td class='label'>Hari / Tanggal</td><td>: {$date}</td></tr>
        <tr><td class='label'>Pimpinan Rapat</td><td>: {$chair}</td></tr>
    </table>

    " . ($minutesNotes ? "<div class='section-title'>I. CATATAN & PEMBAHASAN</div><div style='padding:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin-bottom:20px;'>{$minutesNotes}</div>" : '') . "

    <div class='section-title'>II. DAFTAR PESERTA RAPAT</div>
    <table class='data-table'>
        <thead>
            <tr>
                <th style='width: 40px; text-align:center;'>No</th>
                <th>Nama Peserta</th>
                <th style='width: 160px;'>Jabatan / Peran</th>
                <th style='width: 100px; text-align:center;'>Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            " . ($participantsRows ?: "<tr><td colspan='4' style='text-align:center; color:#94a3b8;'>Tidak ada data peserta</td></tr>") . "
        </tbody>
    </table>

    <div class='section-title'>III. KEPUTUSAN & RENCANA TINDAK LANJUT</div>
    <table class='data-table'>
        <thead>
            <tr>
                <th style='width: 40px; text-align:center;'>No</th>
                <th style='width: 120px;'>Kode</th>
                <th>Butir Keputusan</th>
                <th>Dasar Pertimbangan / Rationale</th>
                <th style='width: 100px; text-align:center;'>Status</th>
            </tr>
        </thead>
        <tbody>
            " . ($decisionsRows ?: "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>Tidak ada butir keputusan</td></tr>") . "
        </tbody>
    </table>

    <table style='width: 100%; margin-top: 40px;'>
        <tr>
            <td style='width: 50%;'></td>
            <td style='width: 50%; text-align: center;'>
                Disahkan oleh,<br><strong>Pimpinan RTM</strong><br><br><br><br>
                <u>{$chair}</u>
            </td>
        </tr>
    </table>
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

        $ptName = htmlspecialchars($cycle->perguruanTinggi?->nama_pt ?? 'Institusi');
        $prodiName = $cycle->programStudi ? 'Program Studi: ' . htmlspecialchars($cycle->programStudi->nama_prodi) : 'Tingkat Institusi / Fakultas';
        $cycleName = htmlspecialchars($cycle->name);
        $code = htmlspecialchars($cycle->code);
        $period = (string) ($cycle->period_year ?? '-');

        $findingsRows = '';
        $no = 1;
        foreach ($cycle->findings as $f) {
            $fCode = htmlspecialchars($f->code);
            $fClass = match ($f->classification) {
                'nonconformity' => "<span style='color:#b91c1c;font-weight:600;'>Ketidaksesuaian ({$f->severity})</span>",
                'opportunity' => "<span style='color:#0369a1;font-weight:600;'>Peluang Perbaikan</span>",
                default => "<span style='color:#d97706;font-weight:600;'>Observasi</span>",
            };
            $cond = nl2br(htmlspecialchars($f->condition));
            $cause = htmlspecialchars($f->cause ?? '-');
            $rec = nl2br(htmlspecialchars($f->recommendation ?? '-'));

            $evItems = '';
            foreach ($f->evidenceLinks as $link) {
                $evTitle = htmlspecialchars($link->evidence?->title ?? 'Bukti');
                $evItems .= "<li style='margin-top:2px;'>{$evTitle} " . ($link->citation_page ? "({$link->citation_page})" : '') . "</li>";
            }
            $evList = $evItems ? "<div style='margin-top:4px;font-size:11px;color:#475569;'><strong>Bukti:</strong><ul style='margin:2px 0 0 16px;padding:0;'>{$evItems}</ul></div>" : '';

            $findingsRows .= "
            <tr>
                <td style='text-align:center;'>{$no}</td>
                <td><strong>{$fCode}</strong><br>{$fClass}</td>
                <td>{$cond}{$evList}</td>
                <td>{$cause}</td>
                <td>{$rec}</td>
            </tr>";
            $no++;
        }

        return "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='utf-8'>
    <title>Laporan Hasil AMI - {$code}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; color: #1e293b; font-size: 12px; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f172a; }
        .header h2 { margin: 4px 0 0 0; font-size: 13px; font-weight: normal; color: #475569; }
        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 4px 8px; vertical-align: top; }
        .meta-table td.label { width: 180px; font-weight: 600; color: #334155; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 25px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.data-table th { background: #f1f5f9; font-weight: 600; color: #1e293b; font-size: 11px; }
        @media print { body { margin: 15mm; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class='no-print' style='background: #e0f2fe; border: 1px solid #0284c7; padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;'>
        <span><strong>Dokumen Siap Cetak:</strong> Laporan Ringkasan Hasil Audit Mutu Internal (AMI)</span>
        <button onclick='window.print()' style='background: #0284c7; color: white; border: none; padding: 6px 14px; border-radius: 4px; font-weight: bold; cursor: pointer;'>Cetak / Simpan PDF</button>
    </div>

    <div class='header'>
        <h1>{$ptName}</h1>
        <h2>LAPORAN HASIL AUDIT MUTU INTERNAL (AMI)</h2>
    </div>

    <table class='meta-table'>
        <tr><td class='label'>Siklus AMI</td><td>: <strong>[{$code}] {$cycleName}</strong></td></tr>
        <tr><td class='label'>Lingkup Unit</td><td>: {$prodiName}</td></tr>
        <tr><td class='label'>Tahun Pelaksanaan</td><td>: {$period}</td></tr>
        <tr><td class='label'>Total Temuan</td><td>: " . $cycle->findings->count() . " Butir Temuan</td></tr>
    </table>

    <table class='data-table'>
        <thead>
            <tr>
                <th style='width: 30px; text-align:center;'>No</th>
                <th style='width: 140px;'>Kode & Klasifikasi</th>
                <th>Kondisi Temuan & Bukti</th>
                <th style='width: 160px;'>Akar Penyebab</th>
                <th style='width: 200px;'>Rekomendasi Auditor</th>
            </tr>
        </thead>
        <tbody>
            " . ($findingsRows ?: "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>Tidak ada temuan pada siklus audit ini.</td></tr>") . "
        </tbody>
    </table>
</body>
</html>";
    }
}
