<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Generators;

use App\Domain\DocumentOutput\Services\AccreditationDocumentExporter;
use App\Models\Accreditation;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;

/**
 * Generator Draf LED (.docx) native dengan format standar dokumen akreditasi resmi.
 *
 * Menghasilkan dokumen Word .docx komprehensif:
 * - Cover Page berstandar institusi (Kop, Judul LED, Identitas Program Studi & PT, Versi Instrumen)
 * - Lembar Identitas Pengusul & Kata Pengantar
 * - Struktur Kriteria (Heading 1) dan Elemen/Sub-Kriteria (Heading 2)
 * - Narasi respons LED berjarak spasi 1.5 standar
 * - Tabel dokumen bukti pendukung lengkap dengan kode, nomor halaman/bab, status verifikasi, dan catatan sitasi
 * - Header & Footer dengan nomor halaman dinamis
 */
final class LedDocumentRenderer
{
    public function __construct(
        private readonly AccreditationDocumentExporter $exporter
    ) {}

    public function generate(Accreditation $accreditation): string
    {
        $adapter = $this->exporter->resolveAdapter($accreditation);
        $data = $adapter->buildLedData($accreditation);

        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();

        // Document defaults
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Define named heading styles
        $phpWord->addTitleStyle(1, [
            'name' => 'Times New Roman',
            'size' => 14,
            'bold' => true,
            'color' => '0F2D6E',
        ], [
            'spaceAfter' => 240,
            'spaceBefore' => 480,
            'borderBottomColor' => '0F2D6E',
            'borderBottomSize' => 12,
        ]);

        $phpWord->addTitleStyle(2, [
            'name' => 'Times New Roman',
            'size' => 12,
            'bold' => true,
            'color' => '1E3A8A',
        ], [
            'spaceAfter' => 160,
            'spaceBefore' => 280,
        ]);

        $phpWord->addTitleStyle(3, [
            'name' => 'Times New Roman',
            'size' => 11,
            'bold' => true,
            'color' => '334155',
        ], [
            'spaceAfter' => 120,
            'spaceBefore' => 200,
        ]);

        // ── 1. Cover Page ─────────────────────────────────────────────
        $cover = $phpWord->addSection([
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
        ]);
        $this->buildCoverPage($cover, $data);
        $cover->addPageBreak();

        // ── 2. Lembar Pendahuluan & Identitas ─────────────────────────
        $intro = $phpWord->addSection([
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(4.0),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
        ]);
        $this->buildIntroPage($intro, $data);
        $intro->addPageBreak();

        // ── 3. Isi Dokumen / Kriteria LED ─────────────────────────────
        $body = $phpWord->addSection([
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(4.0),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
        ]);

        // Header and Footer for body
        $header = $body->addHeader();
        $header->addText(
            'LAPORAN EVALUASI DIRI (LED) — ' . strtoupper((string) ($data['institution_name'] ?? 'PERGURUAN TINGGI')),
            ['size' => 9, 'italic' => true, 'color' => '64748B', 'name' => 'Times New Roman'],
            ['alignment' => Jc::RIGHT]
        );

        $footer = $body->addFooter();
        $footer->addPreserveText(
            'Antigravity i-QMS  |  ' . ($data['accreditation_code'] ?? '') . '  |  Halaman {PAGE} dari {NUMPAGES}',
            ['size' => 9, 'name' => 'Times New Roman', 'color' => '64748B'],
            ['alignment' => Jc::CENTER]
        );

        // Render each Section / Criterion
        foreach ($data['sections'] as $section) {
            $body->addTitle($section['code'] . ': ' . $section['title'], 1);

            if (! empty($section['description'])) {
                $body->addText(
                    $section['description'],
                    ['size' => 11, 'italic' => true, 'name' => 'Times New Roman', 'color' => '475569'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 160]
                );
            }

            foreach ($section['elements'] as $element) {
                // Element heading
                $body->addTitle($element['code'] . ' ' . $element['title'], 2);

                // Meta box
                $metaRun = $body->addTextRun(['alignment' => Jc::LEFT, 'spaceAfter' => 100]);
                $metaRun->addText('Bobot Penilaian: ', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $metaRun->addText(number_format((float) $element['weight'], 2) . '  |  ', ['size' => 10, 'name' => 'Times New Roman']);
                $metaRun->addText('Status Butir: ', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $metaRun->addText(strtoupper((string) $element['status']), ['bold' => true, 'size' => 10, 'name' => 'Times New Roman', 'color' => '0F2D6E']);

                // Narrative text with 1.5 line spacing
                $narrative = filled($element['response_text'])
                    ? $element['response_text']
                    : '[Belum ada narasi evaluasi diri yang diinputkan untuk butir ini.]';

                $body->addText(
                    $narrative,
                    ['size' => 12, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'lineHeight' => 1.5, 'spaceAfter' => 180, 'spaceBefore' => 80]
                );

                // Attached evidence table
                if (! empty($element['evidences'])) {
                    $body->addTitle('Dokumen Bukti Pendukung Terkait', 3);
                    $table = $body->addTable([
                        'borderSize' => 6,
                        'borderColor' => 'CBD5E1',
                        'cellMargin' => 80,
                        'width' => 100 * 50,
                        'unit' => TblWidth::PERCENT,
                    ]);

                    // Header row
                    $table->addRow(400);
                    foreach (['Kode Bukti', 'Nama Dokumen', 'Halaman/Bab Sitasi', 'Status Verifikasi', 'Catatan Relevansi'] as $hdr) {
                        $hCell = $table->addCell(null, ['bgColor' => '0F2D6E']);
                        $hCell->addText($hdr, ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
                    }

                    // Data rows
                    foreach ($element['evidences'] as $ev) {
                        $table->addRow(300);
                        $table->addCell(null)->addText($ev['code'] ?? '-', ['size' => 9, 'bold' => true, 'name' => 'Times New Roman']);
                        $table->addCell(null)->addText($ev['title'] ?? '-', ['size' => 9, 'name' => 'Times New Roman']);
                        $table->addCell(null)->addText($ev['citation_page'] ?? '-', ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
                        $table->addCell(null)->addText(strtoupper((string) ($ev['status'] ?? 'draft')), ['size' => 9, 'bold' => true, 'name' => 'Times New Roman', 'color' => ($ev['status'] ?? '') === 'verified' ? '16A34A' : 'CA8A04'], ['alignment' => Jc::CENTER]);
                        $table->addCell(null)->addText($ev['citation_note'] ?? '-', ['size' => 9, 'italic' => true, 'name' => 'Times New Roman']);
                    }

                    $body->addTextBreak(1);
                }
            }

            $body->addTextBreak(1);
        }

        // Write to temporary file and return binary string
        $tmpFile = tempnam(sys_get_temp_dir(), 'led_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpFile);

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return (string) $content;
    }

    private function buildCoverPage(Section $section, array $data): void
    {
        $section->addTextBreak(4);

        // Document Badge
        $section->addText(
            strtoupper((string) ($data['type'] ?? 'DOKUMEN AKREDITASI')),
            ['name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'color' => '64748B'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        // Main Title
        $section->addText(
            'LAPORAN EVALUASI DIRI (LED)',
            ['name' => 'Times New Roman', 'size' => 20, 'bold' => true, 'color' => '0F2D6E'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 100]
        );

        $section->addText(
            strtoupper((string) ($data['title'] ?? 'Kegiatan Akreditasi')),
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'color' => '1E3A8A'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 240]
        );

        $section->addTextBreak(3);

        // Institution & Study Program
        $section->addText(
            strtoupper((string) ($data['institution_name'] ?? 'Perguruan Tinggi')),
            ['name' => 'Times New Roman', 'size' => 16, 'bold' => true, 'color' => '0F172A'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );

        if (isset($data['study_program_name']) && $data['study_program_name']) {
            $section->addText(
                'PROGRAM STUDI ' . strtoupper((string) $data['study_program_name']),
                ['name' => 'Times New Roman', 'size' => 13, 'bold' => true, 'color' => '0284C7'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
            );
        }

        $section->addTextBreak(4);

        // Metadata block
        foreach ([
            'Versi Instrumen: ' . ($data['version_label'] ?? '-'),
            'Kode Akreditasi: ' . ($data['accreditation_code'] ?? '-'),
            'Rencana Pengajuan: ' . ($data['planned_submission_date'] ?? '-'),
            'Tanggal Dokumen: ' . ($data['generated_at'] ?? date('d F Y')),
        ] as $info) {
            $section->addText(
                $info,
                ['name' => 'Times New Roman', 'size' => 11, 'color' => '475569'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
            );
        }
    }

    private function buildIntroPage(Section $section, array $data): void
    {
        $section->addTitle('KATA PENGANTAR & IDENTITAS PENGUSUL', 1);

        $section->addText(
            'Puji dan syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas terselesaikannya penyusunan Laporan Evaluasi Diri (LED) ini. '
            . 'Dokumen ini disusun sebagai wujud komitmen berkelanjutan dalam penjaminan mutu internal (SPMI) dan evaluasi diri yang komprehensif '
            . 'mengacu pada instrumen akreditasi nasional yang berlaku.',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => Jc::BOTH, 'lineHeight' => 1.5, 'spaceAfter' => 180]
        );

        $section->addText(
            'Laporan ini memuat analisis capaian kinerja institusi/program studi pada seluruh kriteria standar, analisis akar penyebab kendala, '
            . 'serta rencana tindak lanjut strategis guna peningkatan mutu berkelanjutan (Continuous Quality Improvement / CQI).',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => Jc::BOTH, 'lineHeight' => 1.5, 'spaceAfter' => 240]
        );

        // Identity Table
        $section->addTitle('Identitas Pengusul Akreditasi', 2);
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'CBD5E1',
            'cellMargin' => 80,
            'width' => 100 * 50,
            'unit' => TblWidth::PERCENT,
        ]);

        $identityRows = [
            ['Perguruan Tinggi', $data['institution_name'] ?? '-'],
            ['Program Studi', $data['study_program_name'] ?? 'Tingkat Institusi'],
            ['Versi Instrumen', $data['version_label'] ?? '-'],
            ['Kode Registrasi', $data['accreditation_code'] ?? '-'],
            ['Total Kriteria', count($data['sections'] ?? []) . ' Kriteria Standar'],
            ['Tanggal Cetak Dokumen', $data['generated_at'] ?? date('d F Y')],
        ];

        foreach ($identityRows as $idRow) {
            $table->addRow(300);
            $c1 = $table->addCell(3000, ['bgColor' => 'F8FAFC']);
            $c1->addText($idRow[0], ['bold' => true, 'size' => 10, 'name' => 'Times New Roman']);
            $c2 = $table->addCell(7000);
            $c2->addText($idRow[1], ['size' => 10, 'name' => 'Times New Roman']);
        }
    }
}
