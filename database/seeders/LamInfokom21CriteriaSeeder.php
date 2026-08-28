<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccreditationBody;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentRubric;
use App\Models\AssessmentScale;
use App\Models\AssessmentScaleOption;
use App\Models\AssessmentThreshold;
use App\Models\InstrumentFamily;
use App\Models\InstrumentNode;
use App\Models\InstrumentScoringRule;
use App\Models\InstrumentVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Baseline seed LAM INFOKOM 2.1 untuk Program Sarjana.
 *
 * Seeder ini sengaja membuat versi draft. Nilai detail setiap butir wajib
 * direkonsiliasi dengan tiga dokumen sumber sebelum versi dipublikasikan.
 * Jalankan ulang dengan aman: record dicari berdasarkan kode bisnis.
 */
final class LamInfokom21CriteriaSeeder extends Seeder
{
    private const BODY_CODE = 'LAM-INFOKOM';
    private const FAMILY_CODE = 'LAM-INFOKOM-APS';
    private const VERSION_LABEL = 'LAM INFOKOM 2.1 - 2025 - Sarjana';
    private const SCALE_CODE = 'LAM-INFOKOM-SKALA-1-4';

    public function run(): void
    {
        DB::transaction(function (): void {
            $body = AccreditationBody::query()->firstOrCreate(
                ['code' => self::BODY_CODE],
                [
                    'name' => 'Lembaga Akreditasi Mandiri Informatika dan Komputer',
                    'kind' => 'LAM-INFOKOM',
                    'status' => 'active',
                ],
            );

            $family = InstrumentFamily::query()->firstOrCreate(
                ['code' => self::FAMILY_CODE],
                [
                    'accreditation_body_id' => $body->getKey(),
                    'name' => 'Instrumen Akreditasi Program Studi LAM INFOKOM',
                    'scope_type' => 'program_study',
                    'description' => 'Instrumen LAM INFOKOM 2.1 untuk program sarjana.',
                ],
            );

            $version = InstrumentVersion::query()->firstOrCreate(
                ['instrument_family_id' => $family->getKey(), 'version_label' => self::VERSION_LABEL],
                [
                    'status' => 'draft',
                    'source_reference' => 'Tiga dokumen matriks penilaian LAM INFOKOM 2.1 - 2025',
                    'changelog' => [
                        'seed' => self::class,
                        'warning' => 'Rekonsiliasi manual terhadap PDF wajib dilakukan sebelum approval.',
                    ],
                ],
            );

            if (! in_array($version->status, ['draft', 'review', 'pending_review'], true)) {
                throw new RuntimeException('Versi LAM INFOKOM sudah dipublikasikan dan tidak boleh diubah oleh seeder.');
            }

            $scale = $this->seedScale($version);
            $nodes = $this->seedNodes($version);
            $criteria = $this->seedCriteria($version, $nodes);
            $this->seedElementsAndRubrics($version, $criteria, $scale);
            $this->seedStatusThresholds($version);
            $this->seedQualificationRules($version);
        });
    }

    private function seedScale(InstrumentVersion $version): AssessmentScale
    {
        $scale = AssessmentScale::query()->firstOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => self::SCALE_CODE],
            [
                'name' => 'Skala Penilaian LAM INFOKOM 1 sampai 4',
                'scale_type' => 'numeric',
                'min_value' => 1,
                'max_value' => 4,
                'precision' => 2,
            ],
        );

        $options = [
            ['code' => 'LAM-SKOR-1', 'label' => 'Kurang', 'numeric_value' => 1, 'sort_order' => 1],
            ['code' => 'LAM-SKOR-2', 'label' => 'Cukup', 'numeric_value' => 2, 'sort_order' => 2],
            ['code' => 'LAM-SKOR-3', 'label' => 'Baik', 'numeric_value' => 3, 'sort_order' => 3],
            ['code' => 'LAM-SKOR-4', 'label' => 'Sangat Baik', 'numeric_value' => 4, 'sort_order' => 4],
        ];

        foreach ($options as $option) {
            AssessmentScaleOption::query()->firstOrCreate(
                ['assessment_scale_id' => $scale->getKey(), 'code' => $option['code']],
                $option + ['metadata' => ['source_reference' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025']],
            );
        }

        return $scale;
    }

    /** @return array<string, InstrumentNode> */
    private function seedNodes(InstrumentVersion $version): array
    {
        $nodes = [];
        $definitions = [
            ['code' => 'DIM-BUDAYA-MUTU', 'title' => 'Budaya Mutu', 'sort_order' => 1],
            ['code' => 'DIM-RELEVANSI-PENDIDIKAN', 'title' => 'Relevansi Pendidikan', 'sort_order' => 2],
            ['code' => 'DIM-RELEVANSI-PENELITIAN', 'title' => 'Relevansi Penelitian dan Pengabdian kepada Masyarakat', 'sort_order' => 3],
            ['code' => 'DIM-AKUNTABILITAS', 'title' => 'Akuntabilitas, Struktur Organisasi, dan Tata Kelola', 'sort_order' => 4],
        ];

        foreach ($definitions as $definition) {
            $nodes[$definition['code']] = InstrumentNode::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['code']],
                [
                    'node_type' => 'dimension',
                    'title' => $definition['title'],
                    'sort_order' => $definition['sort_order'],
                    'is_required' => true,
                    'metadata' => ['source_reference' => 'Matriks Penilaian Kinerja Program Studi dan Suplemen LAM INFOKOM 2.1 - 2025'],
                ],
            );
        }

        return $nodes;
    }

    /** @param array<string, InstrumentNode> $nodes */
    /** @return array<string, AssessmentCriterion> */
    private function seedCriteria(InstrumentVersion $version, array $nodes): array
    {
        $definitions = [
            ['code' => 'KR-BUDAYA-MUTU', 'node' => 'DIM-BUDAYA-MUTU', 'name' => 'Budaya Mutu', 'sort_order' => 1],
            ['code' => 'KR-RELEVANSI-PENDIDIKAN', 'node' => 'DIM-RELEVANSI-PENDIDIKAN', 'name' => 'Relevansi Pendidikan', 'sort_order' => 2],
            ['code' => 'KR-RELEVANSI-PENELITIAN', 'node' => 'DIM-RELEVANSI-PENELITIAN', 'name' => 'Relevansi Penelitian dan Pengabdian kepada Masyarakat', 'sort_order' => 3],
            ['code' => 'KR-AKUNTABILITAS', 'node' => 'DIM-AKUNTABILITAS', 'name' => 'Akuntabilitas, Struktur Organisasi, dan Tata Kelola', 'sort_order' => 4],
        ];

        $criteria = [];
        foreach ($definitions as $definition) {
            $criteria[$definition['code']] = AssessmentCriterion::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['code']],
                [
                    'instrument_node_id' => $nodes[$definition['node']]->getKey(),
                    'name' => $definition['name'],
                    'sort_order' => $definition['sort_order'],
                    'is_required' => true,
                    'weight' => null,
                    'minimum_score' => 1,
                ],
            );
        }

        return $criteria;
    }

    /** @param array<string, AssessmentCriterion> $criteria */
    private function seedElementsAndRubrics(InstrumentVersion $version, array $criteria, AssessmentScale $scale): void
    {
        $elements = [
            ['code' => 'E-BM-01', 'criterion' => 'KR-BUDAYA-MUTU', 'title' => 'Efektivitas SPMI: Penetapan standar', 'weight' => null],
            ['code' => 'E-BM-02', 'criterion' => 'KR-BUDAYA-MUTU', 'title' => 'Efektivitas SPMI: Pelaksanaan standar', 'weight' => null],
            ['code' => 'E-BM-03', 'criterion' => 'KR-BUDAYA-MUTU', 'title' => 'Efektivitas SPMI: Evaluasi', 'weight' => null],
            ['code' => 'E-BM-04', 'criterion' => 'KR-BUDAYA-MUTU', 'title' => 'Efektivitas SPMI: Pengendalian', 'weight' => null],
            ['code' => 'E-BM-05', 'criterion' => 'KR-BUDAYA-MUTU', 'title' => 'Efektivitas SPMI: Peningkatan', 'weight' => null],
            ['code' => 'E-RP-01', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Jumlah dosen tetap homebase', 'weight' => null],
            ['code' => 'E-RP-02', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Rasio jumlah dosen terhadap mahasiswa', 'weight' => null],
            ['code' => 'E-RP-03', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Persentase kualifikasi dosen', 'weight' => null],
            ['code' => 'E-RP-04', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Jumlah dosen dengan jabatan akademik', 'weight' => null],
            ['code' => 'E-RL-01', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Persentase lulusan', 'weight' => null],
            ['code' => 'E-RL-02', 'criterion' => 'KR-RELEVANSI-PENDIDIKAN', 'title' => 'Persentase kelulusan tepat waktu', 'weight' => null],
            ['code' => 'E-RPEN-01', 'criterion' => 'KR-RELEVANSI-PENELITIAN', 'title' => 'Rasio judul penelitian terhadap dosen', 'weight' => null],
            ['code' => 'E-RPEN-02', 'criterion' => 'KR-RELEVANSI-PENELITIAN', 'title' => 'Rasio publikasi terhadap dosen', 'weight' => null],
            ['code' => 'E-RPEN-03', 'criterion' => 'KR-RELEVANSI-PENELITIAN', 'title' => 'Rasio kegiatan pengabdian kepada masyarakat', 'weight' => null],
            ['code' => 'E-AKT-01', 'criterion' => 'KR-AKUNTABILITAS', 'title' => 'Kelengkapan struktur organisasi dan tata kelola', 'weight' => null],
        ];

        foreach ($elements as $index => $definition) {
            $criterion = $criteria[$definition['criterion']];
            $elementNode = InstrumentNode::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['code']],
                [
                    'parent_id' => $criterion->instrument_node_id,
                    'node_type' => 'element',
                    'title' => $definition['title'],
                    'sort_order' => $index + 1,
                    'is_required' => true,
                    'metadata' => ['source_reference' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025'],
                ],
            );

            $element = AssessmentElement::query()->firstOrCreate(
                ['assessment_criterion_id' => $criterion->getKey(), 'code' => $definition['code']],
                [
                    'instrument_node_id' => $elementNode->getKey(),
                    'title' => $definition['title'],
                    'element_type' => 'mixed',
                    'weight' => $definition['weight'],
                    'sort_order' => $index + 1,
                    'is_required' => true,
                    'metadata' => [
                        'scale_code' => $scale->code,
                        'source_reference' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025',
                        'needs_pdf_reconciliation' => true,
                    ],
                ],
            );

            foreach ($this->rubricDefinitions($element->code) as $rubric) {
                $scaleOption = AssessmentScaleOption::query()
                    ->where('assessment_scale_id', $scale->getKey())
                    ->where('numeric_value', $rubric['score'])
                    ->first();

                if (! $scaleOption) {
                    throw new RuntimeException("Opsi skala {$rubric['score']} tidak ditemukan.");
                }
                AssessmentRubric::query()->firstOrCreate(
                    [
                        'instrument_version_id' => $version->getKey(),
                        'instrument_node_id' => $elementNode->getKey(),
                        'assessment_scale_option_id' => $scaleOption->getKey(),
                    ],
                    [
                        'instrument_node_id' => $elementNode->getKey(),
                        'assessment_scale_option_id' => $scaleOption->getKey(),
                        'min_score' => $rubric['min_score'],
                        'max_score' => $rubric['max_score'],
                        'label' => $rubric['label'],
                        'description' => $rubric['description'],
                        'status' => 'draft',
                        'evidence_expectation' => 'Bukti pendukung harus berupa tautan cloud institusi yang dapat diverifikasi.',
                        'approval_notes' => 'Descriptor wajib direkonsiliasi dengan dokumen sumber sebelum approval.',
                    ],
                );
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function rubricDefinitions(string $elementCode): array
    {
        return [
            ['code' => "RUB-{$elementCode}-1", 'score' => 1, 'label' => 'Kurang', 'min_score' => 1, 'max_score' => 1, 'description' => 'Descriptor skor 1 dari dokumen sumber; lengkapi dengan teks resmi sebelum publish.'],
            ['code' => "RUB-{$elementCode}-2", 'score' => 2, 'label' => 'Cukup', 'min_score' => 2, 'max_score' => 2, 'description' => 'Descriptor skor 2 dari dokumen sumber; lengkapi dengan teks resmi sebelum publish.'],
            ['code' => "RUB-{$elementCode}-3", 'score' => 3, 'label' => 'Baik', 'min_score' => 3, 'max_score' => 3, 'description' => 'Descriptor skor 3 dari dokumen sumber; lengkapi dengan teks resmi sebelum publish.'],
            ['code' => "RUB-{$elementCode}-4", 'score' => 4, 'label' => 'Sangat Baik', 'min_score' => 4, 'max_score' => 4, 'description' => 'Descriptor skor 4 dari dokumen sumber; lengkapi dengan teks resmi sebelum publish.'],
        ];
    }

    private function seedStatusThresholds(InstrumentVersion $version): void
    {
        AssessmentThreshold::query()->firstOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => 'TH-STATUS-TERAKREDITASI'],
            [
                'name' => 'Status Terakreditasi',
                'comparison' => 'between',
                'min_value' => 201,
                'max_value' => 320,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 1,
                'weight' => 100,
                'status' => 'draft',
                'config' => ['percentage_min' => 50.25, 'percentage_max' => 80, 'source_reference' => 'Matriks LAM INFOKOM 2.1 - 2025'],
                'source_reference' => 'Matriks Penilaian Kinerja Program Studi dan Suplemen LAM INFOKOM 2.1 - 2025',
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'between',
                'sequence' => 1,
            ],
        );
    }

    private function seedQualificationRules(InstrumentVersion $version): void
    {
        InstrumentScoringRule::query()->firstOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => 'RULE-STATUS-QUALIFICATION-UNGGUL'],
            [
                'rule_type' => 'status_qualification',
                'expression' => [
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'overall_score', 'operator' => '>=', 'value' => null],
                        ['key' => 'avg_budaya_mutu', 'operator' => '>=', 'value' => 3],
                        ['key' => 'avg_relevansi_pendidikan', 'operator' => '>=', 'value' => 3],
                        ['key' => 'avg_relevansi_penelitian', 'operator' => '>=', 'value' => 3],
                    ],
                    'result' => 'unggul',
                ],
                'parameters' => [
                    'aggregation_keys' => ['budaya_mutu', 'relevansi_pendidikan', 'relevansi_penelitian'],
                    'needs_source_reconciliation' => true,
                    'source_reference' => 'Dokumen Kriteria Unggul LAM INFOKOM 2.1 - 2025',
                ],
            ],
        );
    }
}
