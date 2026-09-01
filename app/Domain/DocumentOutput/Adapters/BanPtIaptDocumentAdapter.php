<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Adapters;

use App\Domain\Accreditation\RuntimeScoringEngine;
use App\Domain\DocumentOutput\Contracts\AccreditationDocumentAdapter;
use App\Models\Accreditation;
use App\Models\AssessmentElement;
use App\Models\EvidenceLink;

final class BanPtIaptDocumentAdapter implements AccreditationDocumentAdapter
{
    public function __construct(
        private readonly RuntimeScoringEngine $scoringEngine
    ) {}

    public function getAccreditationBodyCode(): string
    {
        return 'BAN-PT';
    }

    public function getFamilyCode(): string
    {
        return 'BAN-PT-IAPT';
    }

    public function getInstrumentTitle(): string
    {
        return 'Instrumen Akreditasi Perguruan Tinggi (IAPT 3.0) - BAN-PT';
    }

    public function buildLedData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing([
            'perguruanTinggi',
            'instrumentVersion.assessmentCriteria.elements',
            'sections',
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
                    'response_text' => $response?->response_text ?? 'Belum ada narasi LED.',
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
            'type' => 'BAN-PT-IAPT-LED',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'accreditation_code' => $accreditation->code,
            'title' => $accreditation->title,
            'version_label' => $accreditation->instrumentVersion?->version_label ?? 'IAPT 3.0',
            'planned_submission_date' => $accreditation->planned_submission_date?->format('d/m/Y') ?? '-',
            'sections' => $sections,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildLkpsData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'responses']);

        $tables = [
            [
                'code' => 'Tabel 1',
                'title' => 'Kerjasama Institusi',
                'description' => 'Kerjasama tingkat internasional, nasional, dan lokal.',
                'headers' => ['No', 'Lembaga Mitra', 'Tingkat', 'Bentuk Kegiatan', 'Manfaat/Luaran', 'Bukti Kerjasama'],
                'rows' => [
                    ['1', 'Industri & Mitra Institusi', 'Nasional', 'Pengembangan Tridharma', 'Peningkatan Mutu Lulusan', 'SK Kerjasama'],
                ],
            ],
            [
                'code' => 'Tabel 2',
                'title' => 'Jumlah Mahasiswa dan Daya Tampung Institusi',
                'description' => 'Tren mahasiswa baru, aktif, dan lulusan dalam 3 tahun terakhir.',
                'headers' => ['Tahun Akademik', 'Daya Tampung', 'Pendaftar', 'Lulus Seleksi', 'Mahasiswa Baru', 'Total Mahasiswa Aktif'],
                'rows' => [
                    [date('Y', strtotime('-2 year')), '500', '1250', '480', '450', '1800'],
                    [date('Y', strtotime('-1 year')), '500', '1400', '510', '490', '1920'],
                    [date('Y'), '550', '1600', '540', '520', '2050'],
                ],
            ],
            [
                'code' => 'Tabel 3',
                'title' => 'Profil Dosen Tetap Perguruan Tinggi',
                'description' => 'Kecukupan kualifikasi dosen S3 (Doktor) dan jabatan akademik (Lektor Kepala/Guru Besar).',
                'headers' => ['No', 'Nama Dosen', 'NIDN', 'Pendidikan Tertinggi', 'Jabatan Fungsional', 'Sertifikasi Pendidik', 'Kesesuaian Bidang'],
                'rows' => [
                    ['1', 'Dosen Teladan, Ph.D.', '0012345601', 'S3 (Doktor)', 'Lektor Kepala', 'Bersertifikat', 'Sesuai'],
                    ['2', 'Dosen Utama, M.Kom.', '0012345602', 'S2 (Magister)', 'Lektor', 'Bersertifikat', 'Sesuai'],
                ],
            ],
            [
                'code' => 'Tabel 4',
                'title' => 'Penggunaan Dana dan Sarana Prasarana',
                'description' => 'Alokasi operasional pendidikan, penelitian, PkM, dan investasi sarpras.',
                'headers' => ['Jenis Penggunaan', 'TS-2 (Rp)', 'TS-1 (Rp)', 'TS (Rp)', 'Rata-rata (Rp)'],
                'rows' => [
                    ['Operasional Pendidikan', '2.500.000.000', '2.800.000.000', '3.100.000.000', '2.800.000.000'],
                    ['Penelitian Dosen', '250.000.000', '300.000.000', '350.000.000', '300.000.000'],
                    ['Pengabdian kepada Masyarakat', '150.000.000', '180.000.000', '200.000.000', '176.666.667'],
                    ['Investasi Sarpras IT & Lab', '500.000.000', '650.000.000', '750.000.000', '633.333.333'],
                ],
            ],
        ];

        return [
            'type' => 'BAN-PT-IAPT-LKPT',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'accreditation_code' => $accreditation->code,
            'tables' => $tables,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildScoreSimulationData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'instrumentVersion']);
        $scoreResult = $this->scoringEngine->score($accreditation);

        return [
            'type' => 'BAN-PT-IAPT-SIMULASI-SKOR',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'accreditation_code' => $accreditation->code,
            'version_label' => $accreditation->instrumentVersion?->version_label ?? 'IAPT 3.0',
            'final_score' => $scoreResult['score'],
            'qualification_status' => $scoreResult['qualification']['status'] ?? 'Baik',
            'qualification_passed' => $scoreResult['qualification']['passed'] ?? true,
            'validity_years' => $scoreResult['qualification']['validity_years'] ?? 5,
            'failed_rules' => $scoreResult['qualification']['failed_rules'] ?? [],
            'rules' => $scoreResult['rules'] ?? [],
            'scale_info' => [
                'unggul' => '>= 361 (Unggul)',
                'baik_sekali' => '301 - 360 (Baik Sekali)',
                'baik' => '200 - 300 (Baik)',
                'tidak_terakreditasi' => '< 200 (Tidak Terakreditasi)',
            ],
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }

    public function buildEvidenceMatrixData(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['perguruanTinggi', 'responses.evidenceLinks.evidence.versions.document']);

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
            'type' => 'BAN-PT-IAPT-EVIDENCE-MATRIX',
            'institution_name' => $accreditation->perguruanTinggi?->nama_pt ?? 'Perguruan Tinggi',
            'accreditation_code' => $accreditation->code,
            'total_evidence_links' => count($matrix),
            'verified_count' => collect($matrix)->where('verification_status', 'Terverifikasi')->count(),
            'rows' => $matrix,
            'generated_at' => now()->translatedFormat('d F Y H:i:s'),
        ];
    }
}
