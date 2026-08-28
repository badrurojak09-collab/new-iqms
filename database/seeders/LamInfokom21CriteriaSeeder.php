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
 * Seeder Instrumen Akreditasi Program Studi LAM INFOKOM 2.1 (Program Sarjana).
 *
 * Disusun berdasarkan 3 dokumen resmi:
 * 1. Panduan Penyusunan Laporan Evaluasi Diri (LED) LAM INFOKOM 2.1 (Nov 2025).
 * 2. Matriks Penilaian Kinerja Program Studi dan Suplemen LAM INFOKOM 2.1 (Nov 2025).
 * 3. Peraturan BAN-PT No. 39 Tahun 2025 tentang Standar Terakreditasi Unggul LAM INFOKOM.
 *
 * Total Elemen: 80 Butir Utama + 5 Butir Suplemen (Bobot Total: 400).
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
                    'website' => 'https://laminfokom.or.id',
                    'status' => 'active',
                ],
            );

            $family = InstrumentFamily::query()->firstOrCreate(
                ['code' => self::FAMILY_CODE],
                [
                    'accreditation_body_id' => $body->getKey(),
                    'name' => 'Instrumen Akreditasi Program Studi LAM INFOKOM',
                    'scope_type' => 'program_study',
                    'description' => 'Instrumen Akreditasi Program Studi (APS) 2.1 Lingkup Informatika dan Komputer untuk Program Sarjana.',
                ],
            );

            $version = InstrumentVersion::query()->firstOrCreate(
                ['instrument_family_id' => $family->getKey(), 'version_label' => self::VERSION_LABEL],
                [
                    'status' => 'draft',
                    'source_reference' => 'Peraturan LAM-INFOKOM No. 11/PERLAM/MA/LAM-INFOKOM/X/2025 & BAN-PT No. 39 Tahun 2025',
                    'effective_from' => '2025-11-01',
                    'changelog' => [
                        'version' => '2.1',
                        'jenjang' => 'Sarjana',
                        'total_butir' => 82,
                        'total_bobot' => 400,
                        'sumber' => 'Lampiran 3 (LED), Lampiran 4 (Matriks Penilaian & Suplemen), dan BAN-PT No. 39/2025',
                    ],
                ],
            );

            if (! in_array($version->status, ['draft', 'review', 'pending_review'], true)) {
                throw new RuntimeException('Versi LAM INFOKOM sudah dipublikasikan dan tidak boleh diubah oleh seeder.');
            }

            $scale = $this->seedScale($version);
            $criteriaMap = $this->seedCriteriaHierarchy($version);
            $this->seedAllElementsAndRubrics($version, $criteriaMap, $scale);
            $this->seedStatusThresholds($version);
            $this->seedScoringAndQualificationRules($version);
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

    /**
     * @return array<string, array{node: InstrumentNode, criterion: AssessmentCriterion}>
     */
    private function seedCriteriaHierarchy(InstrumentVersion $version): array
    {
        $criteriaData = [
            'A' => [
                'node_code' => 'NODE-KONDISI-EKSTERNAL',
                'criterion_code' => 'KR-KONDISI-EKSTERNAL',
                'name' => 'Kondisi Eksternal',
                'weight' => 4.0,
                'sort_order' => 1,
            ],
            'B' => [
                'node_code' => 'NODE-PROFIL-UPPS',
                'criterion_code' => 'KR-PROFIL-UPPS',
                'name' => 'Profil Unit Pengelola Program Studi / Analisis Internal',
                'weight' => 4.0,
                'sort_order' => 2,
            ],
            'C1' => [
                'node_code' => 'NODE-BUDAYA-MUTU',
                'criterion_code' => 'KR-BUDAYA-MUTU',
                'name' => 'Kriteria 1: Budaya Mutu',
                'weight' => 40.0,
                'sort_order' => 3,
            ],
            'C2' => [
                'node_code' => 'NODE-RELEVANSI-PENDIDIKAN',
                'criterion_code' => 'KR-RELEVANSI-PENDIDIKAN',
                'name' => 'Kriteria 2: Relevansi Pendidikan',
                'weight' => 120.0,
                'sort_order' => 4,
            ],
            'C3' => [
                'node_code' => 'NODE-RELEVANSI-PENELITIAN',
                'criterion_code' => 'KR-RELEVANSI-PENELITIAN',
                'name' => 'Kriteria 3: Relevansi Penelitian',
                'weight' => 72.0,
                'sort_order' => 5,
            ],
            'C4' => [
                'node_code' => 'NODE-RELEVANSI-PKM',
                'criterion_code' => 'KR-RELEVANSI-PKM',
                'name' => 'Kriteria 4: Relevansi PKM',
                'weight' => 60.0,
                'sort_order' => 6,
            ],
            'C5' => [
                'node_code' => 'NODE-AKUNTABILITAS',
                'criterion_code' => 'KR-AKUNTABILITAS',
                'name' => 'Kriteria 5: Akuntabilitas',
                'weight' => 40.0,
                'sort_order' => 7,
            ],
            'C6' => [
                'node_code' => 'NODE-DIFERENSIASI-MISI',
                'criterion_code' => 'KR-DIFERENSIASI-MISI',
                'name' => 'Kriteria 6: Diferensiasi Misi',
                'weight' => 40.0,
                'sort_order' => 8,
            ],
            'SUP' => [
                'node_code' => 'NODE-SUPLEMEN-PRODI',
                'criterion_code' => 'KR-SUPLEMEN-PRODI',
                'name' => 'Suplemen Program Studi',
                'weight' => 20.0,
                'sort_order' => 9,
            ],
        ];

        $map = [];

        foreach ($criteriaData as $key => $data) {
            $node = InstrumentNode::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $data['node_code']],
                [
                    'node_type' => 'criterion',
                    'title' => $data['name'],
                    'weight' => $data['weight'],
                    'sort_order' => $data['sort_order'],
                    'is_required' => true,
                    'metadata' => [
                        'source' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025',
                    ],
                ],
            );

            $criterion = AssessmentCriterion::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $data['criterion_code']],
                [
                    'instrument_node_id' => $node->getKey(),
                    'name' => $data['name'],
                    'weight' => $data['weight'],
                    'minimum_score' => in_array($key, ['C1', 'C2', 'C3'], true) ? 3.00 : 1.00,
                    'sort_order' => $data['sort_order'],
                    'is_required' => true,
                ],
            );

            $map[$key] = ['node' => $node, 'criterion' => $criterion];
        }

        return $map;
    }

    /**
     * @param array<string, array{node: InstrumentNode, criterion: AssessmentCriterion}> $criteriaMap
     */
    private function seedAllElementsAndRubrics(InstrumentVersion $version, array $criteriaMap, AssessmentScale $scale): void
    {
        $elements = $this->getElementDefinitions();

        $scaleOptions = AssessmentScaleOption::query()
            ->where('assessment_scale_id', $scale->getKey())
            ->get()
            ->keyBy(fn (AssessmentScaleOption $opt) => (int) $opt->numeric_value);

        foreach ($elements as $index => $item) {
            $group = $criteriaMap[$item['criterion_key']];
            $parentCriterion = $group['criterion'];
            $parentNode = $group['node'];

            $elementNode = InstrumentNode::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $item['node_code']],
                [
                    'parent_id' => $parentNode->getKey(),
                    'node_type' => 'element',
                    'title' => $item['title'],
                    'weight' => $item['weight'],
                    'sort_order' => $item['no_urut'],
                    'is_required' => true,
                    'metadata' => [
                        'no_urut' => $item['no_urut'],
                        'no_butir' => $item['no_butir'],
                        'jenis' => $item['jenis'],
                        'syarat_unggul' => $item['syarat_unggul'] ?? null,
                        'source' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025 & BAN-PT No. 39/2025',
                    ],
                ],
            );

            AssessmentElement::query()->firstOrCreate(
                ['assessment_criterion_id' => $parentCriterion->getKey(), 'code' => $item['code']],
                [
                    'instrument_node_id' => $elementNode->getKey(),
                    'title' => $item['title'],
                    'element_type' => match ($item['jenis']) {
                        'I' => 'input',
                        'P' => 'process',
                        'O' => 'outcome',
                        default => 'mixed',
                    },
                    'weight' => $item['weight'],
                    'sort_order' => $item['no_urut'],
                    'is_required' => true,
                    'metadata' => [
                        'no_urut' => $item['no_urut'],
                        'no_butir' => $item['no_butir'],
                        'jenis' => $item['jenis'],
                        'syarat_unggul' => $item['syarat_unggul'] ?? null,
                        'deskriptor' => $item['deskriptor'],
                    ],
                ],
            );

            foreach ([1, 2, 3, 4] as $score) {
                $scaleOption = $scaleOptions->get($score);
                if (! $scaleOption) {
                    continue;
                }

                AssessmentRubric::query()->firstOrCreate(
                    [
                        'instrument_version_id' => $version->getKey(),
                        'instrument_node_id' => $elementNode->getKey(),
                        'assessment_scale_option_id' => $scaleOption->getKey(),
                    ],
                    [
                        'min_score' => $score,
                        'max_score' => $score,
                        'label' => $scaleOption->label,
                        'description' => $item['rubrik'][$score] ?? "Kriteria penilaian skor {$score}",
                        'evidence_expectation' => 'Dokumen kebijakan/bukti sahih, SOP, laporan berkala, atau tautan bukti cloud.',
                        'status' => 'draft',
                    ],
                );
            }
        }
    }

    private function seedStatusThresholds(InstrumentVersion $version): void
    {
        $thresholds = [
            [
                'code' => 'TH-STATUS-TIDAK-TERAKREDITASI',
                'name' => 'Tidak Terakreditasi',
                'comparison' => 'lt',
                'target_value' => 200,
                'min_value' => 0,
                'max_value' => 199.99,
                'pass_score' => 0,
                'fail_score' => 0,
                'minimum_score' => 0,
                'weight' => 0,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Tidak Terakreditasi',
                    'rule_summary' => 'Nilai Akhir < 200',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'lt',
                'sequence' => 1,
            ],
            [
                'code' => 'TH-STATUS-TERAKREDITASI',
                'name' => 'Terakreditasi',
                'comparison' => 'between',
                'min_value' => 200,
                'max_value' => 320.99,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 200,
                'weight' => 100,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Terakreditasi',
                    'rule_summary' => '200 <= Nilai Akhir < 321 atau Nilai Akhir >= 321 tapi tidak memenuhi butir pembatas Unggul',
                    'percentage_min' => 50.25,
                    'percentage_max' => 80.0,
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'between',
                'sequence' => 2,
            ],
            [
                'code' => 'TH-STATUS-UNGGUL-3-TAHUN',
                'name' => 'Terakreditasi Unggul (3 Tahun)',
                'comparison' => 'between',
                'min_value' => 321,
                'max_value' => 360.99,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 321,
                'weight' => 100,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Unggul (3 Tahun)',
                    'masa_berlaku_tahun' => 3,
                    'rule_summary' => '321 <= Nilai Akhir < 361, rerata Kriteria 1, 2, 3 masing-masing >= 3.20, dan setiap butir Kriteria 1, 2, 3 >= 3.00',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'between',
                'sequence' => 3,
            ],
            [
                'code' => 'TH-STATUS-UNGGUL-5-TAHUN',
                'name' => 'Terakreditasi Unggul (5 Tahun)',
                'comparison' => 'gte',
                'target_value' => 361,
                'min_value' => 361,
                'max_value' => 400,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 361,
                'weight' => 100,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Unggul (5 Tahun)',
                    'masa_berlaku_tahun' => 5,
                    'rule_summary' => 'Nilai Akhir >= 361, rerata Kriteria 1, 2, 3 masing-masing >= 3.20, dan setiap butir Kriteria 1, 2, 3 >= 3.00',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'gte',
                'sequence' => 4,
            ],
        ];

        foreach ($thresholds as $th) {
            AssessmentThreshold::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $th['code']],
                $th + ['source_reference' => 'Matriks Penilaian Kinerja LAM INFOKOM 2.1 - 2025 Bab V'],
            );
        }
    }

    private function seedScoringAndQualificationRules(InstrumentVersion $version): void
    {
        $rules = [
            [
                'code' => 'RULE-STATUS-QUALIFICATION-UNGGUL-5-TH',
                'rule_type' => 'status_qualification',
                'expression' => [
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'overall_score', 'operator' => '>=', 'value' => 361],
                        ['key' => 'avg_budaya_mutu', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'avg_relevansi_pendidikan', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'avg_relevansi_penelitian', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'min_item_budaya_mutu', 'operator' => '>=', 'value' => 3.00],
                        ['key' => 'min_item_relevansi_pendidikan', 'operator' => '>=', 'value' => 3.00],
                        ['key' => 'min_item_relevansi_penelitian', 'operator' => '>=', 'value' => 3.00],
                    ],
                    'result' => 'unggul_5_tahun',
                ],
                'parameters' => [
                    'source_reference' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025 Bab V',
                    'label' => 'Unggul (berlaku lima tahun)',
                ],
            ],
            [
                'code' => 'RULE-STATUS-QUALIFICATION-UNGGUL-3-TH',
                'rule_type' => 'status_qualification',
                'expression' => [
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'overall_score', 'operator' => '>=', 'value' => 321],
                        ['key' => 'overall_score', 'operator' => '<', 'value' => 361],
                        ['key' => 'avg_budaya_mutu', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'avg_relevansi_pendidikan', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'avg_relevansi_penelitian', 'operator' => '>=', 'value' => 3.20],
                        ['key' => 'min_item_budaya_mutu', 'operator' => '>=', 'value' => 3.00],
                        ['key' => 'min_item_relevansi_pendidikan', 'operator' => '>=', 'value' => 3.00],
                        ['key' => 'min_item_relevansi_penelitian', 'operator' => '>=', 'value' => 3.00],
                    ],
                    'result' => 'unggul_3_tahun',
                ],
                'parameters' => [
                    'source_reference' => 'Matriks Penilaian LAM INFOKOM 2.1 - 2025 Bab V',
                    'label' => 'Unggul (berlaku tiga tahun)',
                ],
            ],
        ];

        foreach ($rules as $rule) {
            InstrumentScoringRule::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $rule['code']],
                $rule,
            );
        }
    }

    /**
     * Data Lengkap 82 Elemen Penilaian LAM INFOKOM 2.1 Sarjana
     *
     * @return array<int, array<string, mixed>>
     */
    private function getElementDefinitions(): array
    {
        return [
            // A. Kondisi Eksternal
            [
                'criterion_key' => 'A',
                'code' => 'LAM-ELM-01-A',
                'node_code' => 'NODE-LAM-ELM-01-A',
                'no_urut' => 1,
                'no_butir' => 'A',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => 'Kondisi Eksternal',
                'deskriptor' => 'Kemampuan UPPS dalam menganalisis aspek-aspek dalam lingkungan makro dan mikro yang relevan dan dapat mempengaruhi eksistensi dan pengembangan PS maupun UPPS serta mengidentifikasi peluang dan ancaman.',
                'syarat_unggul' => 'Sangat komprehensif',
                'rubrik' => [
                    4 => 'UPPS mampu menganalisis aspek-aspek dalam lingkungan makro dan lingkungan mikro yang relevan dan dapat mempengaruhi eksistensi dan pengembangan PS maupun UPPS, serta mengidentifikasi peluang dan ancaman secara sangat komprehensif.',
                    3 => 'UPPS mampu menganalisis aspek-aspek dalam lingkungan makro dan lingkungan mikro yang relevan dan dapat mempengaruhi eksistensi dan pengembangan PS maupun UPPS, serta mengidentifikasi peluang dan ancaman secara komprehensif.',
                    2 => 'UPPS mampu menganalisis aspek-aspek dalam lingkungan makro dan lingkungan mikro yang relevan dan dapat mempengaruhi eksistensi dan pengembangan PS maupun UPPS, serta mengidentifikasi peluang dan ancaman secara cukup komprehensif.',
                    1 => 'UPPS mampu menganalisis aspek-aspek dalam lingkungan makro dan lingkungan mikro yang relevan dan dapat mempengaruhi eksistensi dan pengembangan PS maupun UPPS, serta mengidentifikasi peluang dan ancaman secara kurang komprehensif.',
                ],
            ],

            // B. Profil UPPS
            [
                'criterion_key' => 'B',
                'code' => 'LAM-ELM-02-B',
                'node_code' => 'NODE-LAM-ELM-02-B',
                'no_urut' => 2,
                'no_butir' => 'B',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => 'Profil Unit Pengelola Program Studi / Analisis Internal',
                'deskriptor' => 'Kemampuan UPPS dan PS dalam menyajikan informasi secara ringkas mengenai sejarah UPPS, VMTS, struktur organisasi, mahasiswa dan lulusan, SDM, keuangan, sarpras, SPMI, serta kinerja UPPS.',
                'syarat_unggul' => 'Ringkas, sangat komprehensif, dan konsisten',
                'rubrik' => [
                    4 => 'UPPS mampu menyajikan seluruh informasi secara ringkas, sangat komprehensif, dan konsisten terhadap data dan informasi yang disampaikan pada masing-masing kriteria.',
                    3 => 'UPPS mampu menyajikan seluruh informasi secara ringkas, komprehensif, dan konsisten terhadap data dan informasi yang disampaikan pada masing-masing kriteria.',
                    2 => 'UPPS mampu menyajikan seluruh informasi secara ringkas, cukup komprehensif, dan konsisten terhadap data dan informasi yang disampaikan pada masing-masing kriteria.',
                    1 => 'UPPS mampu menyajikan seluruh informasi secara ringkas, kurang komprehensif, dan kurang konsisten terhadap data dan informasi yang disampaikan pada masing-masing kriteria.',
                ],
            ],

            // Kriteria 1: Budaya Mutu (10 butir, Bobot 40 = 10%)
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-03-1.1.A',
                'node_code' => 'NODE-LAM-ELM-03-1.1.A',
                'no_urut' => 3,
                'no_butir' => '1.1 A',
                'jenis' => 'I',
                'weight' => 3.0,
                'title' => '1.1 [PENETAPAN] A. Kebijakan, standar, dan indikator tata kelola internal',
                'deskriptor' => 'Ketersediaan kebijakan, standar, dan indikator terkait sistem tata kelola internal UPPS dan/atau PT berikut SOP: (1) Administrasi akademik, (2) Administrasi keuangan, (3) Administrasi SDM, (4) Aspek lain PPEPP.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, dan 3',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan, standar, dan indikator terkait sistem tata kelola internal UPPS dan/atau PT berikut SOP yang mencakup aspek 1, 2, 3, dan 4 disertai bukti-bukti yang sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan, standar, dan indikator terkait sistem tata kelola internal UPPS dan/atau PT berikut SOP yang mencakup aspek 1, 2, 3, dan 4 disertai bukti-bukti yang sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan, standar, dan indikator terkait sistem tata kelola internal UPPS dan/atau PT berikut SOP yang mencakup aspek 1, 2, 3, dan 4 disertai bukti-bukti yang sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan, standar, dan indikator terkait sistem tata kelola internal UPPS dan/atau PT berikut SOP yang mencakup aspek 1, 2, 3, dan 4 disertai bukti-bukti yang sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-04-1.1.B',
                'node_code' => 'NODE-LAM-ELM-04-1.1.B',
                'no_urut' => 4,
                'no_butir' => '1.1 B',
                'jenis' => 'I',
                'weight' => 3.0,
                'title' => '1.1 [PENETAPAN] B. Kebijakan fungsi SPMI dan SDM pelaksana kompeten',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator terkait: (1) Fungsi SPMI, (2) SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT.',
                'syarat_unggul' => 'Memenuhi semua aspek dengan bukti lengkap',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan, standar, dan indikator terkait fungsi SPMI dengan SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT, disertai bukti-bukti yang sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan, standar, dan indikator terkait fungsi SPMI dengan SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT, disertai bukti-bukti yang sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan, standar, dan indikator terkait fungsi SPMI dengan SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT, disertai bukti-bukti yang sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan, standar, dan indikator terkait fungsi SPMI dengan SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT, disertai bukti-bukti yang sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-05-1.2.A',
                'node_code' => 'NODE-LAM-ELM-05-1.2.A',
                'no_urut' => 5,
                'no_butir' => '1.2 A',
                'jenis' => 'P',
                'weight' => 5.0,
                'title' => '1.2 [PELAKSANAAN] A. Efektifitas pelaksanaan tata kelola internal',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan terkait standar dan indikator sistem tata kelola internal UPPS/PT berikut SOP (akademik, keuangan, SDM, PPEPP) didukung laporan tahunan.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, dan 3',
                'rubrik' => [
                    4 => 'Pelaksanaan kegiatan terkait standar tata kelola internal (akademik, keuangan, SDM, PPEPP) secara sangat efektif disertai bukti yang sahih dan sangat lengkap (laporan tahunan).',
                    3 => 'Pelaksanaan kegiatan terkait standar tata kelola internal secara efektif disertai bukti yang sahih dan lengkap.',
                    2 => 'Pelaksanaan kegiatan terkait standar tata kelola internal secara cukup efektif disertai bukti yang sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan kegiatan terkait standar tata kelola internal secara kurang efektif disertai bukti yang sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-06-1.2.B',
                'node_code' => 'NODE-LAM-ELM-06-1.2.B',
                'no_urut' => 6,
                'no_butir' => '1.2 B',
                'jenis' => 'P',
                'weight' => 5.0,
                'title' => '1.2 [PELAKSANAAN] B. Efektifitas pelaksanaan fungsi SPMI dan SDM',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan terkait berfungsinya SPMI dengan SDM yang kompeten sebagai pelaksana di tingkat UPPS dan/atau PT.',
                'syarat_unggul' => 'Memenuhi semua aspek dengan bukti lengkap',
                'rubrik' => [
                    4 => 'Pelaksanaan standar yang menunjukkan berfungsinya SPMI dengan SDM yang kompeten secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan standar yang menunjukkan berfungsinya SPMI dengan SDM yang kompeten secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan standar yang menunjukkan berfungsinya SPMI dengan SDM yang kompeten secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan standar yang menunjukkan berfungsinya SPMI dengan SDM yang kompeten secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-07-1.3.A',
                'node_code' => 'NODE-LAM-ELM-07-1.3.A',
                'no_urut' => 7,
                'no_butir' => '1.3 A',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '1.3 [EVALUASI] A. Efektifitas dan keberkalaan evaluasi tata kelola internal',
                'deskriptor' => 'Efektifitas dan keberkalaan pelaksanaan evaluasi ketercapaian standar sistem tata kelola internal UPPS/PT berikut SOP.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, dan 3',
                'rubrik' => [
                    4 => 'Evaluasi ketercapaian standar tata kelola internal dilaksanakan secara berkala dan sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi ketercapaian standar tata kelola internal dilaksanakan secara berkala dan efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi ketercapaian standar tata kelola internal dilaksanakan secara berkala dan cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi ketercapaian standar tata kelola internal dilaksanakan secara berkala dan kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-08-1.3.B',
                'node_code' => 'NODE-LAM-ELM-08-1.3.B',
                'no_urut' => 8,
                'no_butir' => '1.3 B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '1.3 [EVALUASI] B. Efektifitas evaluasi fungsi SPMI dan SDM pelaksana',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi ketercapaian standar terkait fungsi SPMI dan SDM pelaksana di tingkat UPPS dan/atau PT.',
                'syarat_unggul' => 'Memenuhi semua aspek dengan bukti lengkap',
                'rubrik' => [
                    4 => 'Evaluasi ketercapaian fungsi SPMI dan SDM pelaksana dilaksanakan secara berkala dan sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi ketercapaian fungsi SPMI dan SDM pelaksana dilaksanakan secara berkala dan efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi ketercapaian fungsi SPMI dan SDM pelaksana dilaksanakan secara berkala dan cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi ketercapaian fungsi SPMI dan SDM pelaksana dilaksanakan secara berkala dan kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-09-1.4.A',
                'node_code' => 'NODE-LAM-ELM-09-1.4.A',
                'no_urut' => 9,
                'no_butir' => '1.4 A',
                'jenis' => 'P',
                'weight' => 2.0,
                'title' => '1.4 [PENGENDALIAN] A. Efektifitas tindak lanjut evaluasi tata kelola internal',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut hasil evaluasi ketercapaian standar tata kelola internal UPPS/PT berikut SOP.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, dan 3',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi standar tata kelola internal dilaksanakan secara sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi standar tata kelola internal dilaksanakan secara efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi standar tata kelola internal dilaksanakan secara cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi standar tata kelola internal dilaksanakan secara kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-10-1.4.B',
                'node_code' => 'NODE-LAM-ELM-10-1.4.B',
                'no_urut' => 10,
                'no_butir' => '1.4 B',
                'jenis' => 'P',
                'weight' => 2.0,
                'title' => '1.4 [PENGENDALIAN] B. Efektifitas tindak lanjut evaluasi fungsi SPMI',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut hasil evaluasi ketercapaian standar terkait fungsi SPMI dan SDM pelaksana.',
                'syarat_unggul' => 'Memenuhi semua aspek dengan bukti lengkap',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi fungsi SPMI dan SDM dilaksanakan secara sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi fungsi SPMI dan SDM dilaksanakan secara efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi fungsi SPMI dan SDM dilaksanakan secara cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi fungsi SPMI dan SDM dilaksanakan secara kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-11-1.5.A',
                'node_code' => 'NODE-LAM-ELM-11-1.5.A',
                'no_urut' => 11,
                'no_butir' => '1.5 A',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '1.5 [PENINGKATAN] A. Efektifitas peningkatan standar tata kelola internal',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi standar dan indikator sistem tata kelola internal UPPS/PT berikut SOP.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, dan 3',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi standar tata kelola internal dilaksanakan secara sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi standar tata kelola internal dilaksanakan secara efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi standar tata kelola internal dilaksanakan secara cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi standar tata kelola internal dilaksanakan secara kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C1',
                'code' => 'LAM-ELM-12-1.5.B',
                'node_code' => 'NODE-LAM-ELM-12-1.5.B',
                'no_urut' => 12,
                'no_butir' => '1.5 B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '1.5 [PENINGKATAN] B. Efektifitas peningkatan standar fungsi SPMI',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi standar dan indikator terkait fungsi SPMI dan SDM pelaksana.',
                'syarat_unggul' => 'Memenuhi semua aspek dengan bukti lengkap',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi standar fungsi SPMI dan SDM dilaksanakan secara sangat efektif, disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi standar fungsi SPMI dan SDM dilaksanakan secara efektif, disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi standar fungsi SPMI dan SDM dilaksanakan secara cukup efektif, disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi standar fungsi SPMI dan SDM dilaksanakan secara kurang efektif, disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Kriteria 2: Relevansi Pendidikan (20 butir, Bobot 120 = 30%)
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-13-2.1.A',
                'node_code' => 'NODE-LAM-ELM-13-2.1.A',
                'no_urut' => 13,
                'no_butir' => '2.1.A',
                'jenis' => 'I',
                'weight' => 5.0,
                'title' => '2.1 [PENETAPAN] A. Sarpras, DTPR, Pembiayaan, dan PMB Afirmatif/Inklusif',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator: (1) Sarpras pendidikan, (2) DTPR, (3) Pembiayaan pendidikan, (4) PMB perluasan akses, keragaman, afirmasi, disabilitas.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan, standar, indikator sarpras, DTPR, pembiayaan, dan PMB inklusif/afirmasi disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan, standar, indikator sarpras, DTPR, pembiayaan, dan PMB inklusif/afirmasi disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan, standar, indikator sarpras, DTPR, pembiayaan, dan PMB inklusif/afirmasi disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan, standar, indikator sarpras, DTPR, pembiayaan, dan PMB inklusif/afirmasi disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-14-2.1.B',
                'node_code' => 'NODE-LAM-ELM-14-2.1.B',
                'no_urut' => 14,
                'no_butir' => '2.1.B',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '2.1 [PENETAPAN] B. Kurikulum OBE (KKNI Level 6) & Keterlibatan Stakeholder',
                'deskriptor' => 'Kebijakan dan kurikulum OBE (soft & hard competence level 6 KKNI) serta keterlibatan pemangku kepentingan (stakeholder).',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Tersedianya kurikulum OBE (soft/hard competence KKNI 6) dan keterlibatan stakeholder disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kurikulum OBE dan keterlibatan stakeholder disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kurikulum OBE dan keterlibatan stakeholder disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kurikulum OBE dan keterlibatan stakeholder disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-15-2.1.C',
                'node_code' => 'NODE-LAM-ELM-15-2.1.C',
                'no_urut' => 15,
                'no_butir' => '2.1.C',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '2.1 [PENETAPAN] C. Fleksibilitas Pembelajaran, Suasana Akademik & Beban Belajar',
                'deskriptor' => 'Kebijakan fleksibilitas (luring/daring/hibrida, CBL, PBL, micro-credential, RPL), suasana akademik, asesmen, dan beban belajar.',
                'syarat_unggul' => 'Memenuhi aspek 2, 3, 4, dan sebagian aspek 1',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-16-2.1.D',
                'node_code' => 'NODE-LAM-ELM-16-2.1.D',
                'no_urut' => 16,
                'no_butir' => '2.1.D',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '2.1 [PENETAPAN] D. Prestasi Mahasiswa, Kompetensi Lulusan DUDIKA & Sebaran Kerja',
                'deskriptor' => 'Kebijakan prestasi mahasiswa, rekognisi/apresiasi kompetensi lulusan oleh DUDIKA, dan sebaran kerja lulusan (lokal, nasional, internasional).',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 2, dan sebagian aspek 3',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan standar prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan standar prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan standar prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan standar prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-17-2.2.A',
                'node_code' => 'NODE-LAM-ELM-17-2.2.A',
                'no_urut' => 17,
                'no_butir' => '2.2.A',
                'jenis' => 'P',
                'weight' => 9.0,
                'title' => '2.2 [PELAKSANAAN] A. Pelaksanaan Sarpras, DTPR, Pembiayaan, dan PMB Inklusif',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan sarpras pendidikan, DTPR, pembiayaan pendidikan, dan penerimaan mahasiswa baru inklusif.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Pelaksanaan sarpras, DTPR, pembiayaan, dan PMB secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan sarpras, DTPR, pembiayaan, dan PMB secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan sarpras, DTPR, pembiayaan, dan PMB secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan sarpras, DTPR, pembiayaan, dan PMB secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-18-2.2.B',
                'node_code' => 'NODE-LAM-ELM-18-2.2.B',
                'no_urut' => 18,
                'no_butir' => '2.2.B',
                'jenis' => 'P',
                'weight' => 7.0,
                'title' => '2.2 [PELAKSANAAN] B. Pelaksanaan Kurikulum OBE & Keterlibatan Stakeholder',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan pembelajaran dan rancangan kurikulum OBE serta pelibatan pemangku kepentingan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Pelaksanaan kurikulum OBE (soft/hard competence KKNI 6) dan keterlibatan stakeholder secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan kurikulum OBE dan keterlibatan stakeholder secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan kurikulum OBE dan keterlibatan stakeholder secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan kurikulum OBE dan keterlibatan stakeholder secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-19-2.2.C',
                'node_code' => 'NODE-LAM-ELM-19-2.2.C',
                'no_urut' => 19,
                'no_butir' => '2.2.C',
                'jenis' => 'P',
                'weight' => 7.0,
                'title' => '2.2 [PELAKSANAAN] C. Pelaksanaan Fleksibilitas Pembelajaran & Beban Belajar',
                'deskriptor' => 'Efektifitas pelaksanaan fleksibilitas pembelajaran (luring/daring/hibrida, CBL, PBL, micro-credential, RPL), suasana akademik, asesmen, beban belajar.',
                'syarat_unggul' => 'Memenuhi aspek 2, 3, 4, dan sebagian aspek 1',
                'rubrik' => [
                    4 => 'Pelaksanaan fleksibilitas pembelajaran, suasana akademik, penilaian, dan pemenuhan beban belajar secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-20-2.2.D',
                'node_code' => 'NODE-LAM-ELM-20-2.2.D',
                'no_urut' => 20,
                'no_butir' => '2.2.D',
                'jenis' => 'O',
                'weight' => 30.0,
                'title' => '2.2 [PELAKSANAAN/OUTCOME] D. Capaian Prestasi Mahasiswa, Rekognisi DUDIKA & Sebaran Kerja',
                'deskriptor' => 'Efektifitas capaian prestasi mahasiswa, pengakuan dan apresiasi kompetensi lulusan oleh DUDIKA, serta sebaran kerja lulusan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 2, dan sebagian aspek 3',
                'rubrik' => [
                    4 => 'Pelaksanaan/capaian prestasi mahasiswa, rekognisi DUDIKA, dan sebaran kerja lulusan (lokal/nasional/internasional) secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan/capaian prestasi mahasiswa, rekognisi DUDIKA, dan sebaran kerja lulusan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan/capaian prestasi mahasiswa, rekognisi DUDIKA, dan sebaran kerja lulusan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan/capaian prestasi mahasiswa, rekognisi DUDIKA, dan sebaran kerja lulusan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-21-2.3.A',
                'node_code' => 'NODE-LAM-ELM-21-2.3.A',
                'no_urut' => 21,
                'no_butir' => '2.3.A',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '2.3 [EVALUASI] A. Evaluasi Sarpras, DTPR, Pembiayaan, dan PMB Inklusif',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi berkala terhadap sarpras, DTPR, pembiayaan, dan penerimaan mahasiswa baru inklusif.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Evaluasi ketercapaian sarpras, DTPR, pembiayaan, dan PMB dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi ketercapaian sarpras, DTPR, pembiayaan, dan PMB dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi ketercapaian sarpras, DTPR, pembiayaan, dan PMB dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi ketercapaian sarpras, DTPR, pembiayaan, dan PMB dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-22-2.3.B',
                'node_code' => 'NODE-LAM-ELM-22-2.3.B',
                'no_urut' => 22,
                'no_butir' => '2.3.B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '2.3 [EVALUASI] B. Evaluasi Kurikulum OBE & Masukan Stakeholder',
                'deskriptor' => 'Efektifitas evaluasi ketercapaian kurikulum OBE dan pelibatan masukan pemangku kepentingan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-23-2.3.C',
                'node_code' => 'NODE-LAM-ELM-23-2.3.C',
                'no_urut' => 23,
                'no_butir' => '2.3.C',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => '2.3 [EVALUASI] C. Evaluasi Fleksibilitas Pembelajaran & Beban Belajar',
                'deskriptor' => 'Efektifitas evaluasi ketercapaian fleksibilitas pembelajaran, suasana akademik, penilaian, dan pemenuhan beban belajar.',
                'syarat_unggul' => 'Memenuhi aspek 2, 3, 4, dan sebagian aspek 1',
                'rubrik' => [
                    4 => 'Evaluasi fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-24-2.3.D',
                'node_code' => 'NODE-LAM-ELM-24-2.3.D',
                'no_urut' => 24,
                'no_butir' => '2.3.D',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => '2.3 [EVALUASI] D. Evaluasi Prestasi Mahasiswa, Kompetensi Lulusan & Sebaran Kerja',
                'deskriptor' => 'Efektifitas evaluasi ketercapaian prestasi mahasiswa, apresiasi lulusan DUDIKA, dan sebaran kerja lulusan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 2, dan sebagian aspek 3',
                'rubrik' => [
                    4 => 'Evaluasi prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-25-2.4.A',
                'node_code' => 'NODE-LAM-ELM-25-2.4.A',
                'no_urut' => 25,
                'no_butir' => '2.4.A',
                'jenis' => 'P',
                'weight' => 4.0,
                'title' => '2.4 [PENGENDALIAN] A. Tindak Lanjut Sarpras, DTPR, Pembiayaan, dan PMB',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut hasil evaluasi sarpras, DTPR, pembiayaan pendidikan, dan PMB inklusif.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-26-2.4.B',
                'node_code' => 'NODE-LAM-ELM-26-2.4.B',
                'no_urut' => 26,
                'no_butir' => '2.4.B',
                'jenis' => 'P',
                'weight' => 4.0,
                'title' => '2.4 [PENGENDALIAN] B. Tindak Lanjut Kurikulum OBE & Masukan Stakeholder',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi ketercapaian kurikulum OBE dan pelibatan stakeholder.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi kurikulum OBE dan masukan stakeholder dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-27-2.4.C',
                'node_code' => 'NODE-LAM-ELM-27-2.4.C',
                'no_urut' => 27,
                'no_butir' => '2.4.C',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '2.4 [PENGENDALIAN] C. Tindak Lanjut Fleksibilitas Pembelajaran & Beban Belajar',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut evaluasi fleksibilitas proses pembelajaran, suasana akademik, dan beban belajar.',
                'syarat_unggul' => 'Memenuhi aspek 2, 3, 4, dan sebagian aspek 1',
                'rubrik' => [
                    4 => 'Tindak lanjut evaluasi fleksibilitas pembelajaran, suasana akademik, dan beban belajar dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut evaluasi fleksibilitas pembelajaran, suasana akademik, dan beban belajar dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut evaluasi fleksibilitas pembelajaran, suasana akademik, dan beban belajar dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut evaluasi fleksibilitas pembelajaran, suasana akademik, dan beban belajar dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-28-2.4.D',
                'node_code' => 'NODE-LAM-ELM-28-2.4.D',
                'no_urut' => 28,
                'no_butir' => '2.4.D',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '2.4 [PENGENDALIAN] D. Tindak Lanjut Prestasi Mahasiswa, Kompetensi & Sebaran Kerja',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi prestasi mahasiswa, pengakuan DUDIKA, dan sebaran kerja lulusan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 2, dan sebagian aspek 3',
                'rubrik' => [
                    4 => 'Tindak lanjut evaluasi prestasi mhs, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut evaluasi prestasi mhs, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut evaluasi prestasi mhs, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut evaluasi prestasi mhs, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-29-2.5.A',
                'node_code' => 'NODE-LAM-ELM-29-2.5.A',
                'no_urut' => 29,
                'no_butir' => '2.5.A',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '2.5 [PENINGKATAN] A. Peningkatan Sarpras, DTPR, Pembiayaan, dan PMB Inklusif',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi sarpras pendidikan, DTPR, pembiayaan pendidikan, dan penerimaan mahasiswa baru inklusif.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi sarpras, DTPR, pembiayaan, dan PMB dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-30-2.5.B',
                'node_code' => 'NODE-LAM-ELM-30-2.5.B',
                'no_urut' => 30,
                'no_butir' => '2.5.B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '2.5 [PENINGKATAN] B. Peningkatan Kurikulum OBE & Masukan Stakeholder',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi kurikulum OBE (soft & hard competence level 6 KKNI) dan masukan pemangku kepentingan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Peningkatan kurikulum OBE dan pelibatan stakeholder dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan kurikulum OBE dan pelibatan stakeholder dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan kurikulum OBE dan pelibatan stakeholder dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan kurikulum OBE dan pelibatan stakeholder dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-31-2.5.C',
                'node_code' => 'NODE-LAM-ELM-31-2.5.C',
                'no_urut' => 31,
                'no_butir' => '2.5.C',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => '2.5 [PENINGKATAN] C. Peningkatan Fleksibilitas Pembelajaran & Beban Belajar',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi fleksibilitas pembelajaran, suasana akademik, dan pemenuhan beban belajar.',
                'syarat_unggul' => 'Memenuhi aspek 2, 3, 4, dan sebagian aspek 1',
                'rubrik' => [
                    4 => 'Peningkatan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan fleksibilitas pembelajaran, suasana akademik, penilaian, dan beban belajar dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C2',
                'code' => 'LAM-ELM-32-2.5.D',
                'node_code' => 'NODE-LAM-ELM-32-2.5.D',
                'no_urut' => 32,
                'no_butir' => '2.5.D',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => '2.5 [PENINGKATAN] D. Peningkatan Prestasi Mahasiswa, Kompetensi & Sebaran Kerja',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi prestasi mahasiswa, pengakuan kompetensi lulusan oleh DUDIKA, dan sebaran kerja lulusan.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan memenuhi sebagian aspek 2',
                'rubrik' => [
                    4 => 'Peningkatan prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan prestasi mahasiswa, kompetensi lulusan DUDIKA, dan sebaran kerja dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Kriteria 3: Relevansi Penelitian (15 butir, Bobot 72 = 18%)
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-33-3.1.A',
                'node_code' => 'NODE-LAM-ELM-33-3.1.A',
                'no_urut' => 33,
                'no_butir' => '3.1.A',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '3.1 [PENETAPAN] A. Sarpras, Pembiayaan, Roadmap, dan Pengembangan DTPR Penelitian',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator terkait sarpras penelitian, pembiayaan penelitian, peta jalan penelitian, dan pengembangan DTPR di bidang penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan, standar, indikator sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan, standar, indikator sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan, standar, indikator sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan, standar, indikator sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-34-3.1.B',
                'node_code' => 'NODE-LAM-ELM-34-3.1.B',
                'no_urut' => 34,
                'no_butir' => '3.1.B',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '3.1 [PENETAPAN] B. Implementasi Roadmap, Pelibatan Mahasiswa & DUDIKA',
                'deskriptor' => 'Kebijakan implementasi peta jalan penelitian, pelibatan mahasiswa berdasarkan visi misi keilmuan, dan kebutuhan masyarakat serta DUDIKA.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 3, dan sebagian aspek 2',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-35-3.1.C',
                'node_code' => 'NODE-LAM-ELM-35-3.1.C',
                'no_urut' => 35,
                'no_butir' => '3.1.C',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => '3.1 [PENETAPAN] C. Hibah, Kerjasama, Publikasi, HKI, dan Keberlanjutan Penelitian',
                'deskriptor' => 'Kebijakan perolehan hibah penelitian, kerjasama penelitian, publikasi (lokal, nasional, internasional), perolehan HKI, dan keberlanjutan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4 atau aspek 5',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-36-3.2.A',
                'node_code' => 'NODE-LAM-ELM-36-3.2.A',
                'no_urut' => 36,
                'no_butir' => '3.2.A',
                'jenis' => 'P',
                'weight' => 8.0,
                'title' => '3.2 [PELAKSANAAN] A. Pelaksanaan Sarpras, Pembiayaan, Roadmap, dan DTPR Penelitian',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan sarpras penelitian, DTPR, pembiayaan penelitian, dan peta jalan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Pelaksanaan kegiatan sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan kegiatan sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan kegiatan sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan kegiatan sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-37-3.2.B',
                'node_code' => 'NODE-LAM-ELM-37-3.2.B',
                'no_urut' => 37,
                'no_butir' => '3.2.B',
                'jenis' => 'P',
                'weight' => 6.0,
                'title' => '3.2 [PELAKSANAAN] B. Pelaksanaan Roadmap Penelitian & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas pelaksanaan roadmap penelitian, pelibatan mahasiswa, dan pemenuhan kebutuhan DUDIKA.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 3, dan sebagian aspek 2',
                'rubrik' => [
                    4 => 'Pelaksanaan roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-38-3.2.C',
                'node_code' => 'NODE-LAM-ELM-38-3.2.C',
                'no_urut' => 38,
                'no_butir' => '3.2.C',
                'jenis' => 'O',
                'weight' => 18.0,
                'title' => '3.2 [PELAKSANAAN/OUTCOME] C. Capaian Hibah, Kerjasama, Publikasi, HKI, dan Keberlanjutan',
                'deskriptor' => 'Efektifitas perolehan hibah penelitian, kerjasama penelitian, publikasi ilmiah (lokal, nasional, internasional), perolehan HKI, dan keberlanjutan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4 atau aspek 5',
                'rubrik' => [
                    4 => 'Pelaksanaan/capaian hibah penelitian, kerjasama, publikasi, perolehan HKI, dan keberlanjutan penelitian secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan/capaian hibah penelitian, kerjasama, publikasi, HKI, dan keberlanjutan penelitian secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan/capaian hibah penelitian, kerjasama, publikasi, HKI, dan keberlanjutan penelitian secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan/capaian hibah penelitian, kerjasama, publikasi, HKI, dan keberlanjutan penelitian secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-39-3.3.A',
                'node_code' => 'NODE-LAM-ELM-39-3.3.A',
                'no_urut' => 39,
                'no_butir' => '3.3.A',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '3.3 [EVALUASI] A. Evaluasi Sarpras, Pembiayaan, Roadmap, dan DTPR Penelitian',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi berkala terhadap sarpras penelitian, DTPR, pembiayaan penelitian, dan peta jalan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-40-3.3.B',
                'node_code' => 'NODE-LAM-ELM-40-3.3.B',
                'no_urut' => 40,
                'no_butir' => '3.3.B',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '3.3 [EVALUASI] B. Evaluasi Roadmap Penelitian & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas evaluasi berkala implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 3, dan sebagian aspek 2',
                'rubrik' => [
                    4 => 'Evaluasi implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-41-3.3.C',
                'node_code' => 'NODE-LAM-ELM-41-3.3.C',
                'no_urut' => 41,
                'no_butir' => '3.3.C',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '3.3 [EVALUASI] C. Evaluasi Hibah, Kerjasama, Publikasi, HKI, dan Keberlanjutan',
                'deskriptor' => 'Efektifitas evaluasi berkala perolehan hibah penelitian, kerjasama penelitian, publikasi, HKI, dan keberlanjutan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4 atau aspek 5',
                'rubrik' => [
                    4 => 'Evaluasi perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-42-3.4.A',
                'node_code' => 'NODE-LAM-ELM-42-3.4.A',
                'no_urut' => 42,
                'no_butir' => '3.4.A',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '3.4 [PENGENDALIAN] A. Tindak Lanjut Sarpras, Pembiayaan, Roadmap, dan DTPR Penelitian',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi sarpras penelitian, DTPR, pembiayaan penelitian, dan peta jalan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-43-3.4.B',
                'node_code' => 'NODE-LAM-ELM-43-3.4.B',
                'no_urut' => 43,
                'no_butir' => '3.4.B',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '3.4 [PENGENDALIAN] B. Tindak Lanjut Roadmap Penelitian & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi implementasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 3, dan sebagian aspek 2',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi roadmap penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-44-3.4.C',
                'node_code' => 'NODE-LAM-ELM-44-3.4.C',
                'no_urut' => 44,
                'no_butir' => '3.4.C',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '3.4 [PENGENDALIAN] C. Tindak Lanjut Hibah, Kerjasama, Publikasi, HKI, dan Keberlanjutan',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi perolehan hibah penelitian, kerjasama, publikasi, perolehan HKI, dan keberlanjutan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4 atau aspek 5',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-45-3.5.A',
                'node_code' => 'NODE-LAM-ELM-45-3.5.A',
                'no_urut' => 45,
                'no_butir' => '3.5.A',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => '3.5 [PENINGKATAN] A. Peningkatan Sarpras, Pembiayaan, Roadmap, dan DTPR Penelitian',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi hasil sarpras penelitian, pembiayaan, peta jalan penelitian, dan pengembangan DTPR.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi sarpras penelitian, pembiayaan, peta jalan, dan pengembangan DTPR dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-46-3.5.B',
                'node_code' => 'NODE-LAM-ELM-46-3.5.B',
                'no_urut' => 46,
                'no_butir' => '3.5.B',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '3.5 [PENINGKATAN] B. Peningkatan Roadmap Penelitian & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA.',
                'syarat_unggul' => 'Memenuhi aspek 1 dan 3, dan sebagian aspek 2',
                'rubrik' => [
                    4 => 'Peningkatan peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan peta jalan penelitian, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C3',
                'code' => 'LAM-ELM-47-3.5.C',
                'node_code' => 'NODE-LAM-ELM-47-3.5.C',
                'no_urut' => 47,
                'no_butir' => '3.5.C',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '3.5 [PENINGKATAN] C. Peningkatan Hibah, Kerjasama, Publikasi, HKI, dan Keberlanjutan',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi perolehan hibah penelitian, kerjasama penelitian, publikasi, perolehan HKI, dan keberlanjutan penelitian.',
                'syarat_unggul' => 'Memenuhi aspek 1, 2, 3, dan sebagian aspek 4 atau aspek 5',
                'rubrik' => [
                    4 => 'Peningkatan perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan perolehan hibah, kerjasama, publikasi, HKI, dan keberlanjutan penelitian dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Kriteria 4: Relevansi PkM (15 butir, Bobot 60 = 15%)
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-48-4.1.A',
                'node_code' => 'NODE-LAM-ELM-48-4.1.A',
                'no_urut' => 48,
                'no_butir' => '4.1.A',
                'jenis' => 'I',
                'weight' => 3.0,
                'title' => '4.1 [PENETAPAN] A. Sarpras, DTPR, Pembiayaan, dan Peta Jalan PkM',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator terkait sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM (layanan kepakaran).',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan, standar, indikator sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan, standar, indikator sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan, standar, indikator sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan, standar, indikator sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-49-4.1.B',
                'node_code' => 'NODE-LAM-ELM-49-4.1.B',
                'no_urut' => 49,
                'no_butir' => '4.1.B',
                'jenis' => 'I',
                'weight' => 3.0,
                'title' => '4.1 [PENETAPAN] B. Implementasi Peta Jalan PkM & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Kebijakan implementasi peta jalan PkM, pelibatan mahasiswa berdasarkan visi misi keilmuan, dan kebutuhan masyarakat serta DUDIKA.',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-50-4.1.C',
                'node_code' => 'NODE-LAM-ELM-50-4.1.C',
                'no_urut' => 50,
                'no_butir' => '4.1.C',
                'jenis' => 'I',
                'weight' => 2.0,
                'title' => '4.1 [PENETAPAN] C. Hibah, Kerjasama, Diseminasi, HKI, dan Keberlanjutan PkM',
                'deskriptor' => 'Kebijakan perolehan hibah PkM, kerjasama PkM, diseminasi (lokal, nasional, internasional), perolehan HKI, dan keberlanjutan PkM.',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-51-4.2.A',
                'node_code' => 'NODE-LAM-ELM-51-4.2.A',
                'no_urut' => 51,
                'no_butir' => '4.2.A',
                'jenis' => 'P',
                'weight' => 7.0,
                'title' => '4.2 [PELAKSANAAN] A. Pelaksanaan Sarpras, DTPR, Pembiayaan, dan Peta Jalan PkM',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan sarpras PkM, DTPR, pembiayaan PkM, dan peta jalan PkM (layanan kepakaran).',
                'rubrik' => [
                    4 => 'Pelaksanaan kegiatan sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan kegiatan sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan kegiatan sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan kegiatan sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-52-4.2.B',
                'node_code' => 'NODE-LAM-ELM-52-4.2.B',
                'no_urut' => 52,
                'no_butir' => '4.2.B',
                'jenis' => 'P',
                'weight' => 6.0,
                'title' => '4.2 [PELAKSANAAN] B. Pelaksanaan Peta Jalan PkM & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan peta jalan PkM, pelibatan mahasiswa, dan pemenuhan kebutuhan DUDIKA.',
                'rubrik' => [
                    4 => 'Pelaksanaan kegiatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan kegiatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan kegiatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan kegiatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-53-4.2.C',
                'node_code' => 'NODE-LAM-ELM-53-4.2.C',
                'no_urut' => 53,
                'no_butir' => '4.2.C',
                'jenis' => 'O',
                'weight' => 15.0,
                'title' => '4.2 [PELAKSANAAN/OUTCOME] C. Capaian Hibah, Kerjasama, Diseminasi, HKI, dan Keberlanjutan PkM',
                'deskriptor' => 'Efektifitas capaian hibah PkM, kerjasama PkM, diseminasi ilmiah, perolehan HKI, dan keberlanjutan program PkM.',
                'rubrik' => [
                    4 => 'Pelaksanaan/capaian hibah PkM, kerjasama, diseminasi, perolehan HKI, dan keberlanjutan PkM secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan/capaian hibah PkM, kerjasama, diseminasi, perolehan HKI, dan keberlanjutan PkM secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan/capaian hibah PkM, kerjasama, diseminasi, perolehan HKI, dan keberlanjutan PkM secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan/capaian hibah PkM, kerjasama, diseminasi, perolehan HKI, dan keberlanjutan PkM secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-54-4.3.A',
                'node_code' => 'NODE-LAM-ELM-54-4.3.A',
                'no_urut' => 54,
                'no_butir' => '4.3.A',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '4.3 [EVALUASI] A. Evaluasi Sarpras, DTPR, Pembiayaan, dan Peta Jalan PkM',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi berkala terhadap sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM.',
                'rubrik' => [
                    4 => 'Evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-55-4.3.B',
                'node_code' => 'NODE-LAM-ELM-55-4.3.B',
                'no_urut' => 55,
                'no_butir' => '4.3.B',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '4.3 [EVALUASI] B. Evaluasi Peta Jalan PkM & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas evaluasi berkala implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA.',
                'rubrik' => [
                    4 => 'Evaluasi implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi implementasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-56-4.3.C',
                'node_code' => 'NODE-LAM-ELM-56-4.3.C',
                'no_urut' => 56,
                'no_butir' => '4.3.C',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '4.3 [EVALUASI] C. Evaluasi Hibah, Kerjasama, Diseminasi, HKI, dan Keberlanjutan PkM',
                'deskriptor' => 'Efektifitas evaluasi ketercapaian standar perolehan hibah PkM, kerjasama PkM, diseminasi, HKI, dan keberlanjutan PkM.',
                'rubrik' => [
                    4 => 'Evaluasi perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-57-4.4.A',
                'node_code' => 'NODE-LAM-ELM-57-4.4.A',
                'no_urut' => 57,
                'no_butir' => '4.4.A',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '4.4 [PENGENDALIAN] A. Tindak Lanjut Sarpras, DTPR, Pembiayaan, dan Peta Jalan PkM',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut hasil evaluasi ketercapaian sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM.',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-58-4.4.B',
                'node_code' => 'NODE-LAM-ELM-58-4.4.B',
                'no_urut' => 58,
                'no_butir' => '4.4.B',
                'jenis' => 'P',
                'weight' => 2.0,
                'title' => '4.4 [PENGENDALIAN] B. Tindak Lanjut Peta Jalan PkM & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut evaluasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA.',
                'rubrik' => [
                    4 => 'Tindak lanjut evaluasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut evaluasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut evaluasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut evaluasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-59-4.4.C',
                'node_code' => 'NODE-LAM-ELM-59-4.4.C',
                'no_urut' => 59,
                'no_butir' => '4.4.C',
                'jenis' => 'P',
                'weight' => 2.0,
                'title' => '4.4 [PENGENDALIAN] C. Tindak Lanjut Hibah, Kerjasama, Diseminasi, HKI, dan Keberlanjutan PkM',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM.',
                'rubrik' => [
                    4 => 'Tindak lanjut evaluasi hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut evaluasi hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut evaluasi hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut evaluasi hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-60-4.5.A',
                'node_code' => 'NODE-LAM-ELM-60-4.5.A',
                'no_urut' => 60,
                'no_butir' => '4.5.A',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '4.5 [PENINGKATAN] A. Peningkatan Sarpras, DTPR, Pembiayaan, dan Peta Jalan PkM',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM (layanan kepakaran).',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi sarpras PkM, DTPR, pembiayaan, dan peta jalan PkM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-61-4.5.B',
                'node_code' => 'NODE-LAM-ELM-61-4.5.B',
                'no_urut' => 61,
                'no_butir' => '4.5.B',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => '4.5 [PENINGKATAN] B. Peningkatan Peta Jalan PkM & Pelibatan Mahasiswa/DUDIKA',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi peta jalan PkM, pelibatan mahasiswa, dan kebutuhan masyarakat serta DUDIKA.',
                'rubrik' => [
                    4 => 'Peningkatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan peta jalan PkM, pelibatan mahasiswa, dan kebutuhan DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C4',
                'code' => 'LAM-ELM-62-4.5.C',
                'node_code' => 'NODE-LAM-ELM-62-4.5.C',
                'no_urut' => 62,
                'no_butir' => '4.5.C',
                'jenis' => 'O',
                'weight' => 2.0,
                'title' => '4.5 [PENINGKATAN] C. Peningkatan Hibah, Kerjasama, Diseminasi, HKI, dan Keberlanjutan PkM',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi perolehan hibah PkM, kerjasama PkM, diseminasi, perolehan HKI, dan keberlanjutan PkM.',
                'rubrik' => [
                    4 => 'Peningkatan perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan perolehan hibah PkM, kerjasama, diseminasi, HKI, dan keberlanjutan PkM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Kriteria 5: Akuntabilitas (10 butir, Bobot 40 = 10%)
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-63-5.1.A',
                'node_code' => 'NODE-LAM-ELM-63-5.1.A',
                'no_urut' => 63,
                'no_butir' => '5.1.A',
                'jenis' => 'I',
                'weight' => 3.0,
                'title' => '5.1 [PENETAPAN] A. Tata Kelola Otonom, Transparan, Akuntabel, Sarpras & SDM Profesional',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator tata kelola otonom, transparan, akuntabel, sarpras memadai, dan SDM profesional.',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan standar tata kelola otonom, transparan, akuntabel, sarpras memadai, dan SDM profesional disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan standar tata kelola otonom, transparan, akuntabel, sarpras memadai, dan SDM profesional disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan standar tata kelola otonom, transparan, akuntabel, sarpras memadai, dan SDM profesional disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan standar tata kelola otonom, transparan, akuntabel, sarpras memadai, dan SDM profesional disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-64-5.1.B',
                'node_code' => 'NODE-LAM-ELM-64-5.1.B',
                'no_urut' => 64,
                'no_butir' => '5.1.B',
                'jenis' => 'I',
                'weight' => 2.0,
                'title' => '5.1 [PENETAPAN] B. Audit Mutu Pemenuhan Tupoksi Tata Kelola & Tata Pamong',
                'deskriptor' => 'Ketersediaan kebijakan standar dan indikator audit mutu pemenuhan tupoksi tata kelola dan tata pamong, sarpras, dan SDM.',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan standar audit mutu pemenuhan tupoksi tata kelola/tata pamong, sarpras, dan SDM profesional disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan standar audit mutu pemenuhan tupoksi tata kelola/tata pamong, sarpras, dan SDM profesional disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan standar audit mutu pemenuhan tupoksi tata kelola/tata pamong, sarpras, dan SDM profesional disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan standar audit mutu pemenuhan tupoksi tata kelola/tata pamong, sarpras, dan SDM profesional disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-65-5.2.A',
                'node_code' => 'NODE-LAM-ELM-65-5.2.A',
                'no_urut' => 65,
                'no_butir' => '5.2.A',
                'jenis' => 'P',
                'weight' => 5.0,
                'title' => '5.2 [PELAKSANAAN] A. Pelaksanaan Tata Kelola Otonom, Transparan & Akuntabel',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan standar tata kelola yang otonom, transparan, akuntabel didukung sarpras dan SDM profesional.',
                'rubrik' => [
                    4 => 'Pelaksanaan tata kelola otonom, transparan, akuntabel didukung sarpras dan SDM profesional secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan tata kelola otonom, transparan, akuntabel didukung sarpras dan SDM profesional secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan tata kelola otonom, transparan, akuntabel didukung sarpras dan SDM profesional secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan tata kelola otonom, transparan, akuntabel didukung sarpras dan SDM profesional secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-66-5.2.B',
                'node_code' => 'NODE-LAM-ELM-66-5.2.B',
                'no_urut' => 66,
                'no_butir' => '5.2.B',
                'jenis' => 'P',
                'weight' => 4.0,
                'title' => '5.2 [PELAKSANAAN] B. Pelaksanaan Audit Mutu Tupoksi Tata Kelola & Tata Pamong',
                'deskriptor' => 'Efektifitas pelaksanaan audit mutu pemenuhan tupoksi tata kelola dan tata pamong, sarpras, dan SDM profesional.',
                'rubrik' => [
                    4 => 'Pelaksanaan audit mutu pemenuhan tupoksi tata kelola/pamong, sarpras, dan SDM profesional secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan audit mutu pemenuhan tupoksi tata kelola/pamong, sarpras, dan SDM profesional secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan audit mutu pemenuhan tupoksi tata kelola/pamong, sarpras, dan SDM profesional secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan audit mutu pemenuhan tupoksi tata kelola/pamong, sarpras, dan SDM profesional secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-67-5.3.A',
                'node_code' => 'NODE-LAM-ELM-67-5.3.A',
                'no_urut' => 67,
                'no_butir' => '5.3.A',
                'jenis' => 'O',
                'weight' => 6.0,
                'title' => '5.3 [EVALUASI] A. Evaluasi Tata Kelola Otonom, Sarpras & SDM Profesional',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi berkala terhadap ketercapaian sistem tata kelola otonom, sarpras, dan SDM profesional.',
                'rubrik' => [
                    4 => 'Evaluasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-68-5.3.B',
                'node_code' => 'NODE-LAM-ELM-68-5.3.B',
                'no_urut' => 68,
                'no_butir' => '5.3.B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '5.3 [EVALUASI] B. Evaluasi Audit Mutu Tupoksi Tata Kelola & Tata Pamong',
                'deskriptor' => 'Efektifitas pelaksanaan evaluasi berkala audit mutu pemenuhan tupoksi tata kelola, tata pamong, sarpras, dan SDM.',
                'rubrik' => [
                    4 => 'Evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-69-5.4.A',
                'node_code' => 'NODE-LAM-ELM-69-5.4.A',
                'no_urut' => 69,
                'no_butir' => '5.4.A',
                'jenis' => 'P',
                'weight' => 3.0,
                'title' => '5.4 [PENGENDALIAN] A. Tindak Lanjut Tata Kelola Otonom, Sarpras & SDM Profesional',
                'deskriptor' => 'Efektifitas pelaksanaan tindak lanjut evaluasi tata kelola otonom, sarpras, dan SDM profesional.',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi tata kelola otonom, sarpras, dan SDM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi tata kelola otonom, sarpras, dan SDM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi tata kelola otonom, sarpras, dan SDM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi tata kelola otonom, sarpras, dan SDM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-70-5.4.B',
                'node_code' => 'NODE-LAM-ELM-70-5.4.B',
                'no_urut' => 70,
                'no_butir' => '5.4.B',
                'jenis' => 'P',
                'weight' => 2.0,
                'title' => '5.4 [PENGENDALIAN] B. Tindak Lanjut Audit Mutu Tupoksi Tata Kelola & Tata Pamong',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi audit mutu pemenuhan tupoksi tata kelola dan tata pamong, sarpras, dan SDM.',
                'rubrik' => [
                    4 => 'Tindak lanjut evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut evaluasi audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-71-5.5.A',
                'node_code' => 'NODE-LAM-ELM-71-5.5.A',
                'no_urut' => 71,
                'no_butir' => '5.5.A',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '5.5 [PENINGKATAN] A. Peningkatan Tata Kelola Otonom, Sarpras & SDM Profesional',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi tata kelola otonom secara transparan dan akuntabel, sarpras memadai, dan SDM profesional.',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi tata kelola otonom, sarpras, dan SDM profesional dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi status tata kelola otonom, sarpras, dan SDM profesional dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C5',
                'code' => 'LAM-ELM-72-5.5.B',
                'node_code' => 'NODE-LAM-ELM-72-5.5.B',
                'no_urut' => 72,
                'no_butir' => '5.5.B',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => '5.5 [PENINGKATAN] B. Peningkatan Audit Mutu Tupoksi Tata Kelola & Tata Pamong',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi audit mutu pemenuhan tupoksi tata kelola dan tata pamong, sarpras, dan SDM.',
                'rubrik' => [
                    4 => 'Peningkatan audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM profesional dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM profesional dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM profesional dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan audit mutu tupoksi tata kelola/pamong, sarpras, dan SDM profesional dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Kriteria 6: Diferensiasi Misi (5 butir, Bobot 40 = 10%)
            [
                'criterion_key' => 'C6',
                'code' => 'LAM-ELM-73-6.1',
                'node_code' => 'NODE-LAM-ELM-73-6.1',
                'no_urut' => 73,
                'no_butir' => '6.1',
                'jenis' => 'I',
                'weight' => 5.0,
                'title' => '6.1 [PENETAPAN] Tridarma Mencakup VMTS, Renstra Keilmuan PS & Apresiasi DUDIKA',
                'deskriptor' => 'Ketersediaan kebijakan, standar dan indikator tridarma PT mencakup VMTS, renstra keilmuan PS, dan pengakuan/apresiasi DUDIKA.',
                'rubrik' => [
                    4 => 'Tersedianya kebijakan standar tridarma mencakup VMTS, renstra kekhasan keilmuan PS, dan apresiasi DUDIKA (lokal/nasional/internasional) disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tersedianya kebijakan standar tridarma mencakup VMTS, renstra keilmuan PS, dan apresiasi DUDIKA disertai bukti sahih dan lengkap.',
                    2 => 'Tersedianya kebijakan standar tridarma mencakup VMTS, renstra keilmuan PS, dan apresiasi DUDIKA disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tersedianya kebijakan standar tridarma mencakup VMTS, renstra keilmuan PS, dan apresiasi DUDIKA disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C6',
                'code' => 'LAM-ELM-74-6.2',
                'node_code' => 'NODE-LAM-ELM-74-6.2',
                'no_urut' => 74,
                'no_butir' => '6.2',
                'jenis' => 'P',
                'weight' => 8.0,
                'title' => '6.2 [PELAKSANAAN] Pelaksanaan Tridarma VMTS, Renstra Keilmuan & Apresiasi DUDIKA',
                'deskriptor' => 'Efektifitas pelaksanaan kegiatan standar tridarma mencakup VMTS, rencana strategis ciri khas keilmuan PS, dan pengakuan DUDIKA.',
                'rubrik' => [
                    4 => 'Pelaksanaan standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Pelaksanaan standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Pelaksanaan standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Pelaksanaan standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C6',
                'code' => 'LAM-ELM-75-6.3',
                'node_code' => 'NODE-LAM-ELM-75-6.3',
                'no_urut' => 75,
                'no_butir' => '6.3',
                'jenis' => 'O',
                'weight' => 13.0,
                'title' => '6.3 [EVALUASI] Evaluasi Tridarma VMTS, Renstra Keilmuan & Apresiasi DUDIKA',
                'deskriptor' => 'Efektifitas evaluasi ketercapaian standar tridarma mencakup VMTS, renstra keilmuan PS, dan pengakuan/apresiasi DUDIKA.',
                'rubrik' => [
                    4 => 'Evaluasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan berkala secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Evaluasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan berkala secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Evaluasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan berkala secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Evaluasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan berkala secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C6',
                'code' => 'LAM-ELM-76-6.4',
                'node_code' => 'NODE-LAM-ELM-76-6.4',
                'no_urut' => 76,
                'no_butir' => '6.4',
                'jenis' => 'P',
                'weight' => 4.0,
                'title' => '6.4 [PENGENDALIAN] Tindak Lanjut Tridarma VMTS, Renstra Keilmuan & Apresiasi DUDIKA',
                'deskriptor' => 'Efektifitas tindak lanjut hasil evaluasi ketercapaian standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA.',
                'rubrik' => [
                    4 => 'Tindak lanjut hasil evaluasi tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Tindak lanjut hasil evaluasi tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Tindak lanjut hasil evaluasi tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Tindak lanjut hasil evaluasi tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],
            [
                'criterion_key' => 'C6',
                'code' => 'LAM-ELM-77-6.5',
                'node_code' => 'NODE-LAM-ELM-77-6.5',
                'no_urut' => 77,
                'no_butir' => '6.5',
                'jenis' => 'O',
                'weight' => 10.0,
                'title' => '6.5 [PENINGKATAN] Peningkatan Tridarma VMTS, Renstra Keilmuan & Apresiasi DUDIKA',
                'deskriptor' => 'Efektifitas peningkatan/optimalisasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi masyarakat serta DUDIKA.',
                'rubrik' => [
                    4 => 'Peningkatan/optimalisasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara sangat efektif disertai bukti sahih dan sangat lengkap.',
                    3 => 'Peningkatan/optimalisasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara efektif disertai bukti sahih dan lengkap.',
                    2 => 'Peningkatan/optimalisasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara cukup efektif disertai bukti sahih dan cukup lengkap.',
                    1 => 'Peningkatan/optimalisasi standar tridarma VMTS, renstra keilmuan PS, dan apresiasi DUDIKA dilaksanakan secara kurang efektif disertai bukti sahih tetapi kurang lengkap.',
                ],
            ],

            // Suplemen Program Studi (5 butir, Bobot 20 = 5%)
            [
                'criterion_key' => 'SUP',
                'code' => 'LAM-ELM-78-SUP-1',
                'node_code' => 'NODE-LAM-ELM-78-SUP-1',
                'no_urut' => 78,
                'no_butir' => 'SUP.1',
                'jenis' => 'O',
                'weight' => 4.0,
                'title' => 'Suplemen: Mata Kuliah Inti / Khas Prodi',
                'deskriptor' => 'Ketersediaan dan keselarasan mata kuliah inti/khas program studi yang mencerminkan profil lulusan.',
                'rubrik' => [
                    4 => 'Mata kuliah inti/khas prodi dirancang sangat mutakhir, terstruktur sangat komprehensif, dan selaras penuh dengan capaian profil lulusan bidang Infokom.',
                    3 => 'Mata kuliah inti/khas prodi dirancang mutakhir, terstruktur komprehensif, dan selaras dengan capaian profil lulusan.',
                    2 => 'Mata kuliah inti/khas prodi dirancang cukup mutakhir dan cukup selaras dengan profil lulusan.',
                    1 => 'Mata kuliah inti/khas prodi kurang mutakhir dan kurang selaras dengan profil lulusan.',
                ],
            ],
            [
                'criterion_key' => 'SUP',
                'code' => 'LAM-ELM-79-SUP-2',
                'node_code' => 'NODE-LAM-ELM-79-SUP-2',
                'no_urut' => 79,
                'no_butir' => 'SUP.2',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => 'Suplemen: Mata Kuliah Domain Spesifik & Lingkungan Prodi Infokom',
                'deskriptor' => 'Ketersediaan mata kuliah domain spesifik dan lingkungan penerapan ilmu program studi infokom.',
                'rubrik' => [
                    4 => 'Mata kuliah domain spesifik dan lingkungan prodi infokom mencakup studi kasus riil industri secara sangat komprehensif.',
                    3 => 'Mata kuliah domain spesifik dan lingkungan prodi infokom mencakup studi kasus industri secara komprehensif.',
                    2 => 'Mata kuliah domain spesifik mencakup studi kasus industri secara cukup komprehensif.',
                    1 => 'Mata kuliah domain spesifik kurang mencakup studi kasus industri.',
                ],
            ],
            [
                'criterion_key' => 'SUP',
                'code' => 'LAM-ELM-80-SUP-3',
                'node_code' => 'NODE-LAM-ELM-80-SUP-3',
                'no_urut' => 80,
                'no_butir' => 'SUP.3',
                'jenis' => 'O',
                'weight' => 3.0,
                'title' => 'Suplemen: Mata Kuliah Matematika / Metode Analisis Kuantitatif Relevan',
                'deskriptor' => 'Kedalaman materi dan relevansi mata kuliah terkait Matematika, metode kuantitatif, atau analisis komputasi relevan.',
                'rubrik' => [
                    4 => 'Mata kuliah matematika/metode kuantitatif sangat relevan, terintegrasi mendalam dengan pemodelan komputasi dan algoritma mutakhir.',
                    3 => 'Mata kuliah matematika/metode kuantitatif relevan dan terintegrasi dengan pemodelan komputasi.',
                    2 => 'Mata kuliah matematika/metode kuantitatif cukup relevan dengan bidang infokom.',
                    1 => 'Mata kuliah matematika/metode kuantitatif kurang relevan dengan bidang infokom.',
                ],
            ],
            [
                'criterion_key' => 'SUP',
                'code' => 'LAM-ELM-81-SUP-4',
                'node_code' => 'NODE-LAM-ELM-81-SUP-4',
                'no_urut' => 81,
                'no_butir' => 'SUP.4',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => 'Suplemen: Proyek Utama (Capstone Project) yang Relevan',
                'deskriptor' => 'Kualitas, kompleksitas, dan relevansi pelaksanaan Capstone Project dalam memecahkan permasalahan nyata di bidang informatika dan komputer.',
                'rubrik' => [
                    4 => 'Capstone project wajib multi-disiplin, berskala industri/kompleks, dengan luaran produk nyata/teruji yang digunakan oleh pengguna eksternal secara sangat memuaskan.',
                    3 => 'Capstone project berorientasi pemecahan masalah industri/kompleks dengan luaran produk nyata yang berfungsi baik.',
                    2 => 'Capstone project cukup memenuhi kaidah perancangan sistem dan berfungsi dengan cukup baik.',
                    1 => 'Capstone project kurang memenuhi kompleksitas perancangan bidang infokom.',
                ],
            ],
            [
                'criterion_key' => 'SUP',
                'code' => 'LAM-ELM-82-SUP-5',
                'node_code' => 'NODE-LAM-ELM-82-SUP-5',
                'no_urut' => 82,
                'no_butir' => 'SUP.5',
                'jenis' => 'O',
                'weight' => 5.0,
                'title' => 'Suplemen: Pengembangan Bidang Infokom yang Digunakan di Masyarakat',
                'deskriptor' => 'Hasil karya atau pengembangan bidang infokom karya sivitas akademika yang diimplementasikan dan dimanfaatkan langsung oleh masyarakat/industri.',
                'rubrik' => [
                    4 => 'Pengembangan bidang infokom berhasil diimplementasikan secara luas, berdaya guna tinggi, dan mendapat pengakuan resmi/adopsi dari mitra masyarakat atau DUDIKA.',
                    3 => 'Pengembangan bidang infokom berhasil diimplementasikan dan dimanfaatkan oleh mitra masyarakat/industri.',
                    2 => 'Pengembangan bidang infokom cukup dimanfaatkan oleh kelompok masyarakat tertentu.',
                    1 => 'Pengembangan bidang infokom belum dimanfaatkan secara nyata oleh masyarakat/industri.',
                ],
            ],
        ];
    }
}
