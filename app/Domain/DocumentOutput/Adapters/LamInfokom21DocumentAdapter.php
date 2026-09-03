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
        $accreditation->loadMissing([
            'perguruanTinggi',
            'programStudi',
            'responses',
            'instrumentVersion.lkpsTemplates.columns',
            'lkpsDatasets.template.columns',
        ]);

        $prodiName = $accreditation->programStudi?->nama_prodi ?? 'Teknik Informatika / Sistem Informasi';
        $templates = $accreditation->instrumentVersion?->lkpsTemplates?->sortBy('sort_order') ?? collect();

        $tables = [];

        if ($templates->isNotEmpty()) {
            foreach ($templates as $template) {
                $dataset = $accreditation->lkpsDatasets->firstWhere('lkps_template_id', $template->getKey());
                $columns = $template->columns->sortBy('sort_order');
                $headers = $columns->map(fn ($c) => $c->label . ($c->unit ? " ({$c->unit})" : ''))->values()->all();

                $rows = [];
                if ($dataset && ! empty($dataset->rows_data)) {
                    foreach ($dataset->rows_data as $rIdx => $row) {
                        $cells = [];
                        foreach ($columns as $col) {
                            $val = $row[$col->column_key] ?? '';
                            if (is_numeric($val) && $col->data_type === 'decimal' && $col->decimal_scale !== null) {
                                $val = number_format((float) $val, (int) $col->decimal_scale);
                            }
                            $cells[] = (string) $val;
                        }
                        $rows[] = $cells;
                    }
                }

                if (empty($rows)) {
                    // Provide an empty row with default indicators
                    $rows[] = $columns->map(fn ($c) => '—')->all();
                }

                $tables[] = [
                    'code' => $template->code,
                    'title' => $template->name,
                    'description' => $template->description ?: "Tabel data kuantitatif {$template->name}",
                    'headers' => $headers,
                    'rows' => $rows,
                ];
            }
        }

        // Fallback default tables if no instrument templates defined
        if (empty($tables)) {
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
            ];
        }

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
