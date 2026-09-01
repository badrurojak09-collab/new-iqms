<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Adapters;

use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Domain\DocumentOutput\Contracts\AccreditationDocumentAdapter;
use App\Models\Accreditation;
use App\Models\AssessmentElement;
use App\Models\EvidenceLink;

final class LamInfokom21DocumentAdapter implements AccreditationDocumentAdapter
{
    public function __construct(
        private readonly RuntimeScoringEngine $scoringEngine
    ) {}

    public function getAccreditationBodyCode(): string
    {
        return 'LAM-INFOKOM';
    }

    public function getFamilyCode(): string
    {
        return 'LAM-INFOKOM-APS';
    }

    public function getInstrumentTitle(): string
    {
        return 'Instrumen Akreditasi Program Studi LAM-INFOKOM 2.1 (Tahun 2025 - Sarjana)';
    }

    public function buildLedData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing([
            'perguruanTinggi',
            'programStudi',
            'instrumentVersion.assessmentCriteria.elements',
            'responses.evidenceLinks.evidence',
        ]);

        $sections = [];
        $criteria = $accreditation->instrumentVersion?->assessmentCriteria?->sortBy('sort_order') ?? collect();

        foreach ($criteria as $criterion) {
            $elements = [];
            foreach ($criterion->elements->sortBy('sort_order') as $element) {
                $response = $accreditation->responses->first(fn ($r) => $r->response_key === $element->code || (int) $r->instrument_node_id === (int) $element->instrument_node_id);
                $evidenceLinks = $response?->evidenceLinks ?? collect();

                $elements[] = [
                    'code' => $element->code,
                    'title' => $element->title,
                    'weight' => (float) ($element->weight ?? 0),
                    'response_text' => $response?->response_text ?? 'Belum ada narasi LED untuk elemen ini.',
                    'status' => $response?->status ?? 'draft',
                    'evidences' => $evidenceLinks->map(fn (EvidenceLink $link) => [
                        'title' => $link->evidence?->title ?? 'Dokumen Bukti',
                        'code' => $link->evidence?->code ?? '-',
                        'status' => $link->evidence?->status ?? 'draft',
                        'citation_page' => $link->citation_page,
                        'citation_note' => $link->citation_note,
                        'url' => $link->evidence?->versions?->first()?->document?->external_url ?? null,
                    ])->all(),
                ];
            }

            $sections[] = [
                'code' => $criterion->code,
                'title' => $criterion->title,
                'description' => $criterion->description,
                'weight' => (float) ($criterion->weight ?? 0),
                'elements' => $elements,
            ];
        }

        return [
            'type' => 'LAM-INFOKOM-2.1-LED',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'study_program_name' => $accreditation->programStudi?->nama_prodi ?? 'Program Studi',
            'accreditation_code' => $accreditation->code,
            'title' => $accreditation->title,
            'version_label' => $accreditation->instrumentVersion?->version_label ?? 'LAM INFOKOM 2.1',
            'planned_submission_date' => $accreditation->planned_submission_date?->format('d/m/Y') ?? '-',
            'sections' => $sections,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildLkpsData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'programStudi', 'responses']);

        $prodiName = $accreditation->programStudi?->nama_prodi ?? 'Teknik Informatika / Sistem Informasi';

        $tables = [
            [
                'code' => 'Tabel 1',
                'title' => 'Kerjasama Tridharma Bidang Infokom',
                'description' => 'Kerjasama pendidikan, penelitian, dan PkM bidang informatika/komputer dengan industri IT dan instansi.',
                'headers' => ['No', 'Lembaga Mitra', 'Tingkat', 'Bentuk Kegiatan', 'Bukti Kerjasama (MOU/MOA)', 'Durasi/Masa Berlaku'],
                'rows' => [
                    ['1', 'Industri Software & IT Solusi', 'Nasional', 'Magang MBKM & Penyerapan Lulusan', 'PKS/TI/2025/01', '3 Tahun'],
                    ['2', 'Asosiasi Profesi Informatika / Komputer', 'Nasional', 'Uji Kompetensi & Sertifikasi BNSP', 'PKS/TI/2025/02', '5 Tahun'],
                ],
            ],
            [
                'code' => 'Tabel 2.a',
                'title' => 'Jumlah Mahasiswa Baru dan Mahasiswa Aktif',
                'description' => 'Seleksi dan daya tampung mahasiswa program studi.',
                'headers' => ['Tahun Akademik', 'Daya Tampung', 'Pendaftar', 'Lulus Seleksi', 'Mahasiswa Baru Reguler', 'Total Mahasiswa Aktif'],
                'rows' => [
                    [date('Y', strtotime('-2 year')), '150', '450', '160', '150', '580'],
                    [date('Y', strtotime('-1 year')), '150', '520', '165', '155', '610'],
                    [date('Y'), '160', '600', '175', '160', '640'],
                ],
            ],
            [
                'code' => 'Tabel 3.a.1',
                'title' => 'Dosen Tetap Program Studi (DTPS) Bidang Infokom',
                'description' => 'Kualifikasi, jabatan fungsional, dan sertifikasi pendidik/profesi DTPS.',
                'headers' => ['No', 'Nama Dosen', 'NIDN', 'Pendidikan S2/S3', 'Bidang Keahlian', 'Jabatan Fungsional', 'Sertifikasi Pendidik', 'Sertifikasi Profesi IT (BNSP/Internasional)'],
                'rows' => [
                    ['1', 'Dr. Dosen Informatika, S.Kom., M.Kom.', '0412345601', 'S3 Ilmu Komputer', 'Artificial Intelligence', 'Lektor Kepala', 'Ya', 'Certified Data Scientist'],
                    ['2', 'Dosen Sistem Informasi, M.Kom.', '0412345602', 'S2 Sistem Informasi', 'Enterprise Architecture', 'Lektor', 'Ya', 'Certified Scrum Master'],
                    ['3', 'Dosen Rekayasa Perangkat Lunak, M.T.', '0412345603', 'S2 Teknik Informatika', 'Software Engineering', 'Lektor', 'Ya', 'AWS Certified Solutions Architect'],
                ],
            ],
            [
                'code' => 'Tabel 3.b',
                'title' => 'Kinerja dan Publikasi Ilmiah DTPS Bidang Infokom',
                'description' => 'Publikasi jurnal internasional terindeks Scopus/WoS, SINTA, dan HKI.',
                'headers' => ['No', 'Jenis Publikasi / Karya', 'TS-2', 'TS-1', 'TS', 'Jumlah'],
                'rows' => [
                    ['1', 'Jurnal Internasional Terindeks (Scopus/WoS)', '3', '5', '8', '16'],
                    ['2', 'Jurnal Nasional Terakreditasi (SINTA 1-2)', '4', '6', '7', '17'],
                    ['3', 'Jurnal Nasional Terakreditasi (SINTA 3-6)', '8', '10', '12', '30'],
                    ['4', 'Hak Cipta / Paten / HKI Software', '5', '7', '9', '21'],
                ],
            ],
            [
                'code' => 'Tabel 4',
                'title' => 'Sarana & Laboratorium Komputer',
                'description' => 'Kecukupan laboratorium komputasi, rasio komputer per mahasiswa, dan lisensi software.',
                'headers' => ['No', 'Nama Laboratorium', 'Jumlah Unit PC', 'Spesifikasi Utama', 'Software Berlisensi / Open Source', 'Kondisi'],
                'rows' => [
                    ['1', 'Lab Rekayasa Perangkat Lunak & Web', '40 Unit', 'Intel Core i7 / 16GB RAM / SSD', 'VS Code, Docker, MySQL, IntelliJ', 'Sangat Baik'],
                    ['2', 'Lab Artificial Intelligence & Data Science', '35 Unit', 'Intel Core i7 / GPU RTX 4060 / 32GB RAM', 'Python, TensorFlow, PyTorch, Jupyter', 'Sangat Baik'],
                    ['3', 'Lab Jaringan & Keamanan Siber', '30 Unit', 'Cisco Router/Switch + Server PC', 'Wireshark, Kali Linux, Packet Tracer', 'Baik'],
                ],
            ],
            [
                'code' => 'Tabel 5.a',
                'title' => 'Kurikulum, Capaian Pembelajaran (CPL), dan MBKM',
                'description' => 'Struktur mata kuliah berbasis standar ACM/IEEE Curricula dan konversi sks MBKM.',
                'headers' => ['Semester', 'Kode MK', 'Nama Mata Kuliah', 'Bobot SKS', 'Kelompok MK', 'Kesesuaian CPL ACM/IEEE'],
                'rows' => [
                    ['1', 'INF101', 'Dasar Pemrograman Komputer', '4', 'Wajib Keilmuan', 'Programming Fundamentals'],
                    ['2', 'INF102', 'Struktur Data dan Algoritma', '4', 'Wajib Keilmuan', 'Algorithms & Complexity'],
                    ['3', 'INF201', 'Basis Data & SQL', '3', 'Wajib Keilmuan', 'Information Management'],
                    ['4', 'INF202', 'Rekayasa Perangkat Lunak', '3', 'Wajib Keilmuan', 'Software Engineering'],
                    ['5', 'INF301', 'Kecerdasan Buatan', '3', 'Wajib Keilmuan', 'Intelligent Systems'],
                    ['6', 'INF302', 'Magang Industri IT (MBKM)', '20', 'Pilihan MBKM', 'Professional Practice'],
                ],
            ],
            [
                'code' => 'Tabel 8.d',
                'title' => 'Waktu Tunggu dan Kesesuaian Bidang Kerja Lulusan Infokom',
                'description' => 'Hasil tracer study waktu tunggu mendapatkan pekerjaan dan kesesuaian bidang IT.',
                'headers' => ['Tahun Lulus', 'Jumlah Lulusan', 'Lulusan Terlacak', 'Waktu Tunggu < 3 Bulan', 'Waktu Tunggu 3-6 Bulan', 'Bekerja Bidang Infokom (%)'],
                'rows' => [
                    [date('Y', strtotime('-2 year')), '110', '98', '65', '28', '89.5%'],
                    [date('Y', strtotime('-1 year')), '125', '112', '78', '30', '92.0%'],
                ],
            ],
        ];

        return [
            'type' => 'LAM-INFOKOM-2.1-LKPS',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'study_program_name' => $prodiName,
            'accreditation_code' => $accreditation->code,
            'tables' => $tables,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildScoreSimulationData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'programStudi', 'instrumentVersion']);
        $scoreResult = $this->scoringEngine->score($accreditation);

        return [
            'type' => 'LAM-INFOKOM-2.1-SIMULASI-SKOR',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'study_program_name' => $accreditation->programStudi?->nama_prodi ?? 'Program Studi',
            'accreditation_code' => $accreditation->code,
            'version_label' => $accreditation->instrumentVersion?->version_label ?? 'LAM INFOKOM 2.1 (Sarjana)',
            'final_score' => $scoreResult['score'],
            'qualification_status' => $scoreResult['qualification']['status'] ?? 'Baik',
            'qualification_passed' => $scoreResult['qualification']['passed'] ?? true,
            'validity_years' => $scoreResult['qualification']['validity_years'] ?? 5,
            'failed_rules' => $scoreResult['qualification']['failed_rules'] ?? [],
            'rules' => $scoreResult['rules'] ?? [],
            'scale_info' => [
                'unggul' => '>= 361 & Lolos Syarat Perlu Unggul (Unggul)',
                'baik_sekali' => '301 - 360 & Lolos Syarat Perlu Terakreditasi (Baik Sekali)',
                'baik' => '200 - 300 & Lolos Syarat Perlu Terakreditasi (Baik)',
                'tidak_terakreditasi' => '< 200 / Gagal Syarat Perlu Terakreditasi',
            ],
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildEvidenceMatrixData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'programStudi', 'responses.evidenceLinks.evidence.versions.document']);

        $matrix = [];
        foreach ($accreditation->responses as $response) {
            foreach ($response->evidenceLinks as $link) {
                $evidence = $link->evidence;
                $version = $evidence?->versions?->first();
                $document = $version?->document;

                $matrix[] = [
                    'response_key' => $response->response_key,
                    'evidence_code' => $evidence?->code ?? '-',
                    'evidence_title' => $evidence?->title ?? '-',
                    'relation_type' => $link->relation_type ?? 'primary_evidence',
                    'citation_page' => $link->citation_page ?? '-',
                    'citation_note' => $link->citation_note ?? '-',
                    'is_required' => $link->is_required ? 'Wajib' : 'Opsional',
                    'verification_status' => $evidence?->status === 'verified' ? 'Terverifikasi' : 'Draft/Belum Terverifikasi',
                    'external_url' => $document?->external_url ?? '-',
                    'storage_provider' => $document?->storage_provider ?? 'Cloud Storage',
                ];
            }
        }

        return [
            'type' => 'LAM-INFOKOM-2.1-EVIDENCE-MATRIX',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'study_program_name' => $accreditation->programStudi?->nama_prodi ?? 'Program Studi',
            'accreditation_code' => $accreditation->code,
            'total_evidence_links' => count($matrix),
            'verified_count' => collect($matrix)->where('verification_status', 'Terverifikasi')->count(),
            'rows' => $matrix,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }
}
