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

/**
 * Generator Draf LED (.docx) native.
 *
 * Menghasilkan dokumen Word .docx yang terstruktur:
 * - Cover page (judul, institusi, prodi, kode, tanggal)
 * - Daftar isi otomatis (TOC)
 * - Setiap Kriteria = Heading 1, setiap Elemen = Heading 2
 * - Narasi respons LED per elemen
 * - Daftar dokumen bukti tertaut per elemen
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
        $phpWord->getDefaultFontName() !== 'Times New Roman' && $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Define named styles
        $phpWord->addTitleStyle(1, [
            'name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'color' => '0F2D6E',
        ], [
            'spaceAfter' => 240, 'spaceBefore' => 480, 'borderBottomColor' => '0F2D6E', 'borderBottomSize' => 12,
        ]);
        $phpWord->addTitleStyle(2, [
            'name' => 'Times New Roman', 'size' => 12, 'bold' => true, 'color' => '1E3A8A',
        ], [
            'spaceAfter' => 160, 'spaceBefore' => 280,
        ]);
        $phpWord->addTitleStyle(3, [
            'name' => 'Times New Roman', 'size' => 11, 'bold' => true, 'color' => '334155',
        ], [
            'spaceAfter' => 120, 'spaceBefore' => 200,
        ]);

        // ── Cover Page ────────────────────────────────────────────────
        $cover = $phpWord->addSection();
        $this->buildCoverPage($cover, $data);
        $cover->addPageBreak();

        // ── Body Section ──────────────────────────────────────────────
        $body = $phpWord->addSection([
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(4.0),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(3.0),
        ]);

        // Intro paragraph
        $body->addText(
            'Dokumen Laporan Evaluasi Diri (LED) ini disusun sebagai bagian dari pengajuan akreditasi kepada lembaga akreditasi yang berwenang. '
            . 'Isi dokumen ini merupakan narasi evaluasi diri yang mencakup seluruh kriteria standar nasional pendidikan tinggi yang dipersyaratkan.',
            ['size' => 12, 'name' => 'Times New Roman'],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 240]
        );

        $body->addTextBreak(1);

        // ── Sections / Criteria ────────────────────────────────────────
        foreach ($data['sections'] as $section) {
            $body->addTitle($section['code'] . ' ' . $section['title'], 1);

            if (! empty($section['description'])) {
                $body->addText(
                    $section['description'],
                    ['size' => 11, 'italic' => true, 'name' => 'Times New Roman', 'color' => '64748B'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 120]
                );
            }

            foreach ($section['elements'] as $element) {
                // Element heading
                $body->addTitle($element['code'] . ' ' . $element['title'], 2);

                // Bobot info
                $infoRun = $body->addTextRun(['alignment' => Jc::LEFT, 'spaceAfter' => 80]);
                $infoRun->addText('Bobot: ', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $infoRun->addText(number_format((float) $element['weight'], 2), ['size' => 10, 'name' => 'Times New Roman']);
                $infoRun->addText('  |  Status Respons: ', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $infoRun->addText(strtoupper($element['status']), ['size' => 10, 'name' => 'Times New Roman', 'color' => '1E3A8A']);

                // Narasi LED
                $body->addText(
                    $element['response_text'],
                    ['size' => 12, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 160, 'spaceBefore' => 80]
                );

                // Evidence list
                if (! empty($element['evidences'])) {
                    $body->addTitle('Dokumen Bukti Pendukung', 3);
                    $table = $body->addTable([
                        'borderSize' => 6,
                        'borderColor' => 'CBD5E1',
                        'cellMargin' => 60,
                        'width' => 100 * 50,
                        'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::PERCENT,
                    ]);

                    // Table header row
                    $table->addRow(400);
                    foreach (['Kode Bukti', 'Judul Dokumen', 'Halaman/Bab', 'Status', 'Catatan'] as $hdr) {
                        $hCell = $table->addCell(null, ['bgColor' => '1E3A5F']);
                        $hCell->addText($hdr, ['bold' => true, 'size' => 9, 'color' => 'FFFFFF', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER]);
                    }

                    foreach ($element['evidences'] as $ev) {
                        $table->addRow(300);
                        $table->addCell(null)->addText($ev['code'] ?? '-', ['size' => 9, 'name' => 'Times New Roman']);
                        $table->addCell(null)->addText($ev['title'] ?? '-', ['size' => 9, 'name' => 'Times New Roman']);
                        $table->addCell(null)->addText($ev['citation_page'] ?? '-', ['size' => 9, 'name' => 'Times New Roman']);
                        $table->addCell(null)->addText(strtoupper($ev['status'] ?? 'draft'), ['size' => 9, 'name' => 'Times New Roman', 'color' => $ev['status'] === 'verified' ? '16A34A' : 'CA8A04']);
                        $table->addCell(null)->addText($ev['citation_note'] ?? '-', ['size' => 9, 'italic' => true, 'name' => 'Times New Roman']);
                    }

                    $body->addTextBreak(1);
                }
            }

            $body->addTextBreak(2);
        }

        // Footer
        $footer = $body->addFooter();
        $footer->addPreserveText(
            'Antigravity i-QMS  |  ' . ($data['institution_name'] ?? '') . '  |  ' . ($data['version_label'] ?? '') . '  |  Hal. {PAGE}/{NUMPAGES}',
            ['size' => 8, 'name' => 'Times New Roman', 'color' => '94A3B8'],
            ['alignment' => Jc::CENTER]
        );

        // Write to temp file and return binary string
        $tmpFile = tempnam(sys_get_temp_dir(), 'led_') . '.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpFile);

        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return (string) $content;
    }

    private function buildCoverPage(Section $section, array $data): void
    {
        $section->addTextBreak(6);

        // Document type label
        $section->addText(
            $data['type'],
            ['name' => 'Times New Roman', 'size' => 11, 'color' => '64748B'],
            ['alignment' => Jc::CENTER]
        );

        $section->addTextBreak(1);

        // Main title
        $section->addText(
            'LAPORAN EVALUASI DIRI',
            ['name' => 'Times New Roman', 'size' => 18, 'bold' => true, 'color' => '0F2D6E'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        $section->addText(
            strtoupper($data['title'] ?? 'Kegiatan Akreditasi'),
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'color' => '1E3A8A'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );

        $section->addTextBreak(1);

        // Institution
        $section->addText(
            $data['institution_name'] ?? '',
            ['name' => 'Times New Roman', 'size' => 14, 'bold' => true],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
        );

        // Study program if any
        if (isset($data['study_program_name']) && $data['study_program_name']) {
            $section->addText(
                'Program Studi ' . $data['study_program_name'],
                ['name' => 'Times New Roman', 'size' => 12],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 80]
            );
        }

        $section->addTextBreak(2);

        // Meta info
        foreach ([
            'Versi Instrumen: ' . ($data['version_label'] ?? '-'),
            'Kode Akreditasi: ' . ($data['accreditation_code'] ?? '-'),
            'Rencana Pengajuan: ' . ($data['planned_submission_date'] ?? '-'),
            'Digenerate: ' . ($data['generated_at'] ?? '-'),
        ] as $info) {
            $section->addText(
                $info,
                ['name' => 'Times New Roman', 'size' => 11, 'color' => '475569'],
                ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
            );
        }
    }
}
