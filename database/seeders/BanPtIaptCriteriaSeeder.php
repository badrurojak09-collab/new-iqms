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
 * Seeder Instrumen Akreditasi Perguruan Tinggi (IAPT) BAN-PT (9 Kriteria).
 *
 * Disusun berdasarkan standar resmi BAN-PT:
 * - Peraturan BAN-PT No. 3 Tahun 2019 / No. 5 Tahun 2019 / No. 27 Tahun 2024 tentang Instrumen Akreditasi Perguruan Tinggi (IAPT).
 * - Sembilan Kriteria Standar Nasional Pendidikan Tinggi (SN-Dikti):
 *   1. Visi, Misi, Tujuan, dan Strategi (VMTS)
 *   2. Tata Pamong, Tata Kelola, dan Kerjasama
 *   3. Mahasiswa
 *   4. Sumber Daya Manusia (SDM)
 *   5. Keuangan, Sarana, dan Prasarana
 *   6. Pendidikan
 *   7. Penelitian
 *   8. Pengabdian kepada Masyarakat (PkM)
 *   9. Luaran dan Capaian Tridharma
 *   Plus Analisis Kondisi Eksternal, Profil Institusi, dan Program Pengembangan.
 *
 * Total Bobot: 400.00 Poin (Skala 1 - 4).
 * Peringkat: Unggul (>=361), Baik Sekali (301-360), Baik (200-300), Tidak Terakreditasi (<200).
 */
final class BanPtIaptCriteriaSeeder extends Seeder
{
    private const BODY_CODE = 'BAN-PT';
    private const FAMILY_CODE = 'BAN-PT-IAPT';
    private const VERSION_LABEL = 'BAN-PT IAPT 3.0 - Perguruan Tinggi';
    private const SCALE_CODE = 'BAN-PT-SKALA-1-4';

    public function run(): void
    {
        DB::transaction(function (): void {
            $body = AccreditationBody::query()->firstOrCreate(
                ['code' => self::BODY_CODE],
                [
                    'name' => 'Badan Akreditasi Nasional Perguruan Tinggi',
                    'kind' => 'national',
                    'website' => 'https://www.banpt.or.id',
                    'status' => 'active',
                ],
            );

            $family = InstrumentFamily::query()->firstOrCreate(
                ['code' => self::FAMILY_CODE],
                [
                    'accreditation_body_id' => $body->getKey(),
                    'name' => 'Instrumen Akreditasi Perguruan Tinggi (IAPT)',
                    'scope_type' => 'institution',
                    'description' => 'Instrumen Akreditasi Perguruan Tinggi (IAPT) 9 Kriteria Standar Nasional BAN-PT.',
                ],
            );

            $version = InstrumentVersion::query()->firstOrCreate(
                ['instrument_family_id' => $family->getKey(), 'version_label' => self::VERSION_LABEL],
                [
                    'status' => 'draft',
                    'source_reference' => 'Peraturan BAN-PT No. 3/2019, No. 5/2019 & No. 27/2024 tentang IAPT 9 Kriteria',
                    'effective_from' => '2024-01-01',
                    'changelog' => [
                        'version' => '3.0 / 9 Kriteria',
                        'lingkup' => 'Perguruan Tinggi (Institusi)',
                        'total_kriteria' => 9,
                        'total_bobot' => 400,
                        'sumber' => 'Pedoman Penilaian & Matriks Penilaian IAPT BAN-PT',
                    ],
                ],
            );

            if (! in_array($version->status, ['draft', 'review', 'pending_review'], true)) {
                throw new RuntimeException('Versi BAN-PT IAPT sudah dipublikasikan dan tidak boleh diubah oleh seeder.');
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
                'name' => 'Skala Penilaian BAN-PT 1 sampai 4',
                'scale_type' => 'numeric',
                'min_value' => 1,
                'max_value' => 4,
                'precision' => 2,
            ],
        );

        $options = [
            ['code' => 'BANPT-SKOR-1', 'label' => 'Kurang', 'numeric_value' => 1, 'sort_order' => 1],
            ['code' => 'BANPT-SKOR-2', 'label' => 'Cukup', 'numeric_value' => 2, 'sort_order' => 2],
            ['code' => 'BANPT-SKOR-3', 'label' => 'Baik', 'numeric_value' => 3, 'sort_order' => 3],
            ['code' => 'BANPT-SKOR-4', 'label' => 'Sangat Baik', 'numeric_value' => 4, 'sort_order' => 4],
        ];

        foreach ($options as $option) {
            AssessmentScaleOption::query()->firstOrCreate(
                ['assessment_scale_id' => $scale->getKey(), 'code' => $option['code']],
                $option + ['metadata' => ['source_reference' => 'Matriks Penilaian IAPT BAN-PT']],
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
                'node_code' => 'BANPT-NODE-KONDISI-EKSTERNAL',
                'criterion_code' => 'BANPT-KR-KONDISI-EKSTERNAL',
                'name' => 'Kondisi Eksternal & Profil Perguruan Tinggi',
                'weight' => 8.0,
                'sort_order' => 1,
            ],
            'K1' => [
                'node_code' => 'BANPT-NODE-KRITERIA-1-VMTS',
                'criterion_code' => 'BANPT-KR-1-VMTS',
                'name' => 'Kriteria 1: Visi, Misi, Tujuan, dan Strategi',
                'weight' => 20.0,
                'sort_order' => 2,
            ],
            'K2' => [
                'node_code' => 'BANPT-NODE-KRITERIA-2-TATA-PAMONG',
                'criterion_code' => 'BANPT-KR-2-TATA-PAMONG',
                'name' => 'Kriteria 2: Tata Pamong, Tata Kelola, dan Kerjasama',
                'weight' => 50.0,
                'sort_order' => 3,
            ],
            'K3' => [
                'node_code' => 'BANPT-NODE-KRITERIA-3-MAHASISWA',
                'criterion_code' => 'BANPT-KR-3-MAHASISWA',
                'name' => 'Kriteria 3: Mahasiswa',
                'weight' => 30.0,
                'sort_order' => 4,
            ],
            'K4' => [
                'node_code' => 'BANPT-NODE-KRITERIA-4-SDM',
                'criterion_code' => 'BANPT-KR-4-SDM',
                'name' => 'Kriteria 4: Sumber Daya Manusia',
                'weight' => 60.0,
                'sort_order' => 5,
            ],
            'K5' => [
                'node_code' => 'BANPT-NODE-KRITERIA-5-KEUANGAN-SARPRAS',
                'criterion_code' => 'BANPT-KR-5-KEUANGAN-SARPRAS',
                'name' => 'Kriteria 5: Keuangan, Sarana, dan Prasarana',
                'weight' => 40.0,
                'sort_order' => 6,
            ],
            'K6' => [
                'node_code' => 'BANPT-NODE-KRITERIA-6-PENDIDIKAN',
                'criterion_code' => 'BANPT-KR-6-PENDIDIKAN',
                'name' => 'Kriteria 6: Pendidikan',
                'weight' => 70.0,
                'sort_order' => 7,
            ],
            'K7' => [
                'node_code' => 'BANPT-NODE-KRITERIA-7-PENELITIAN',
                'criterion_code' => 'BANPT-KR-7-PENELITIAN',
                'name' => 'Kriteria 7: Penelitian',
                'weight' => 40.0,
                'sort_order' => 8,
            ],
            'K8' => [
                'node_code' => 'BANPT-NODE-KRITERIA-8-PKM',
                'criterion_code' => 'BANPT-KR-8-PKM',
                'name' => 'Kriteria 8: Pengabdian kepada Masyarakat',
                'weight' => 30.0,
                'sort_order' => 9,
            ],
            'K9' => [
                'node_code' => 'BANPT-NODE-KRITERIA-9-LUARAN-TRIDHARMA',
                'criterion_code' => 'BANPT-KR-9-LUARAN-TRIDHARMA',
                'name' => 'Kriteria 9: Luaran dan Capaian Tridharma',
                'weight' => 40.0,
                'sort_order' => 10,
            ],
            'PENGEMBANGAN' => [
                'node_code' => 'BANPT-NODE-ANALISIS-PENGEMBANGAN',
                'criterion_code' => 'BANPT-KR-ANALISIS-PENGEMBANGAN',
                'name' => 'Analisis dan Program Pengembangan Institusi',
                'weight' => 12.0,
                'sort_order' => 11,
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
                        'source' => 'Matriks Penilaian IAPT BAN-PT',
                    ],
                ],
            );

            $criterion = AssessmentCriterion::query()->firstOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $data['criterion_code']],
                [
                    'instrument_node_id' => $node->getKey(),
                    'name' => $data['name'],
                    'weight' => $data['weight'],
                    'minimum_score' => in_array($key, ['K1', 'K2', 'K4', 'K6', 'K9'], true) ? 3.00 : 1.00,
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

        foreach ($elements as $item) {
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
                        'syarat_perlu_peringkat' => $item['syarat_perlu_peringkat'] ?? null,
                        'source' => 'Matriks Penilaian IAPT BAN-PT',
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
                        'syarat_perlu_peringkat' => $item['syarat_perlu_peringkat'] ?? null,
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
                        'evidence_expectation' => 'Dokumen resmi institusi, SK rektor/yayasan, laporan kinerja, atau tautan bukti cloud yang terverifikasi.',
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
                'code' => 'BANPT-TH-TIDAK-TERAKREDITASI',
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
                    'rule_summary' => 'Skor Akhir < 200',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'lt',
                'sequence' => 1,
            ],
            [
                'code' => 'BANPT-TH-BAIK',
                'name' => 'Terakreditasi Baik',
                'comparison' => 'between',
                'min_value' => 200,
                'max_value' => 300.99,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 200,
                'weight' => 100,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Baik',
                    'rule_summary' => '200 <= Skor Akhir < 301',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'between',
                'sequence' => 2,
            ],
            [
                'code' => 'BANPT-TH-BAIK-SEKALI',
                'name' => 'Terakreditasi Baik Sekali',
                'comparison' => 'between',
                'min_value' => 301,
                'max_value' => 360.99,
                'pass_score' => 1,
                'fail_score' => 0,
                'minimum_score' => 301,
                'weight' => 100,
                'status' => 'draft',
                'config' => [
                    'status_label' => 'Baik Sekali',
                    'rule_summary' => '301 <= Skor Akhir < 361 dengan pemenuhan syarat perlu peringkat Baik Sekali (SPMI, Jabatan Dosen, Akreditasi PS)',
                ],
                'direction' => 'higher_is_better',
                'aggregation_key' => 'overall_score',
                'aggregation_operator' => 'between',
                'sequence' => 3,
            ],
            [
                'code' => 'BANPT-TH-UNGGUL',
                'name' => 'Terakreditasi Unggul',
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
                    'status_label' => 'Unggul',
                    'rule_summary' => 'Skor Akhir >= 361 dengan pemenuhan syarat perlu peringkat Unggul (SPMI efektif PPEPP, Rasio & Kualifikasi Dosen Doktor/Lektor Kepala, Luaran Publikasi)',
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
                $th + ['source_reference' => 'Pedoman Penilaian IAPT BAN-PT 9 Kriteria'],
            );
        }
    }

    private function seedScoringAndQualificationRules(InstrumentVersion $version): void
    {
        $rules = [
            [
                'code' => 'BANPT-RULE-QUALIFICATION-UNGGUL',
                'rule_type' => 'status_qualification',
                'expression' => [
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'overall_score', 'operator' => '>=', 'value' => 361],
                        ['key' => 'skor_spmi_efektif', 'operator' => '>=', 'value' => 3.50],
                        ['key' => 'skor_kualifikasi_sdm', 'operator' => '>=', 'value' => 3.25],
                        ['key' => 'skor_luaran_tridharma', 'operator' => '>=', 'value' => 3.25],
                    ],
                    'result' => 'unggul',
                ],
                'parameters' => [
                    'source_reference' => 'Syarat Perlu Peringkat Unggul IAPT BAN-PT',
                    'label' => 'Unggul',
                ],
            ],
            [
                'code' => 'BANPT-RULE-QUALIFICATION-BAIK-SEKALI',
                'rule_type' => 'status_qualification',
                'expression' => [
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'overall_score', 'operator' => '>=', 'value' => 301],
                        ['key' => 'skor_spmi_efektif', 'operator' => '>=', 'value' => 3.00],
                        ['key' => 'skor_kualifikasi_sdm', 'operator' => '>=', 'value' => 2.75],
                    ],
                    'result' => 'baik_sekali',
                ],
                'parameters' => [
                    'source_reference' => 'Syarat Perlu Peringkat Baik Sekali IAPT BAN-PT',
                    'label' => 'Baik Sekali',
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
     * Data Lengkap Elemen Penilaian BAN-PT IAPT (9 Kriteria Perguruan Tinggi).
     * Total Bobot: 400.00 Poin.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getElementDefinitions(): array
    {
        return [
            // A. Kondisi Eksternal & Profil Institusi (Bobot: 8.0)
            [
                'criterion_key' => 'A',
                'code' => 'BANPT-ELM-01-KONDISI-EKSTERNAL',
                'node_code' => 'BANPT-NODE-ELM-01',
                'no_urut' => 1,
                'no_butir' => 'A.1',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => 'Analisis Kondisi Eksternal Institusi',
                'deskriptor' => 'Kemampuan perguruan tinggi dalam memetakan posisi strategis, tantangan makro dan mikro, peluang dan ancaman eksternal yang relevan.',
                'rubrik' => [
                    4 => 'Perguruan tinggi mampu menganalisis lingkungan makro dan mikro secara sangat komprehensif, tajam, serta menetapkan posisi strategis yang visioner.',
                    3 => 'Perguruan tinggi mampu menganalisis lingkungan makro dan mikro secara komprehensif dan menetapkan posisi strategis yang tepat.',
                    2 => 'Perguruan tinggi cukup mampu menganalisis lingkungan eksternal namun belum terintegrasi utuh dengan rencana strategis.',
                    1 => 'Analisis kondisi eksternal sangat terbatas dan kurang relevan dengan pengembangan institusi.',
                ],
            ],
            [
                'criterion_key' => 'A',
                'code' => 'BANPT-ELM-02-PROFIL-PT',
                'node_code' => 'BANPT-NODE-ELM-02',
                'no_urut' => 2,
                'no_butir' => 'A.2',
                'jenis' => 'I',
                'weight' => 4.0,
                'title' => 'Profil dan Sejarah Singkat Perguruan Tinggi',
                'deskriptor' => 'Konsistensi dan keutuhan penyajian informasi profil, tata nilai, sejarah perkembangan, dan capaian kinerja perguruan tinggi.',
                'rubrik' => [
                    4 => 'Profil perguruan tinggi disajikan secara ringkas, sangat komprehensif, runut, dan konsisten terhadap data pada seluruh kriteria penilaian.',
                    3 => 'Profil disajikan secara komprehensif dan konsisten dengan data kriteria pendukung.',
                    2 => 'Profil cukup lengkap namun terdapat inkonsistensi minor terhadap data kriteria.',
                    1 => 'Profil kurang komprehensif dan banyak ketidaksesuaian data.',
                ],
            ],

            // Kriteria 1: Visi, Misi, Tujuan, dan Strategi (Bobot: 20.0)
            [
                'criterion_key' => 'K1',
                'code' => 'BANPT-ELM-03-VMTS-KESELARASAN',
                'node_code' => 'BANPT-NODE-ELM-03',
                'no_urut' => 3,
                'no_butir' => '1.1',
                'jenis' => 'I',
                'weight' => 8.0,
                'title' => 'Kejelasan, Realistis, dan Keselarasan VMTS Institusi',
                'deskriptor' => 'Kejelasan visi keilmuan institusi, misi tridharma, tujuan terukur, dan strategi pencapaian yang selaras dengan rencana strategis (Renstra).',
                'syarat_perlu_peringkat' => 'Kejelasan dan keselarasan visi misi',
                'rubrik' => [
                    4 => 'VMTS sangat jelas, realistis, futuristik, memiliki keunikan pembeda (*distinctiveness*), dan selaras penuh dari tingkat institusi hingga program studi.',
                    3 => 'VMTS jelas, realistis, dan selaras dari tingkat institusi hingga program studi.',
                    2 => 'VMTS cukup jelas namun keselarasan antar unit kerja belum sepenuhnya optimal.',
                    1 => 'VMTS kurang jelas dan tidak menggambarkan arah pengembangan perguruan tinggi yang terencana.',
                ],
            ],
            [
                'criterion_key' => 'K1',
                'code' => 'BANPT-ELM-04-VMTS-SOSIALISASI',
                'node_code' => 'BANPT-NODE-ELM-04',
                'no_urut' => 4,
                'no_butir' => '1.2',
                'jenis' => 'P',
                'weight' => 6.0,
                'title' => 'Mekanisme Penyusunan, Sosialisasi, dan Pemahaman Pemangku Kepentingan',
                'deskriptor' => 'Keterlibatan pemangku kepentingan internal dan eksternal dalam perumusan serta tingkat pemahaman sivitas akademika terhadap VMTS.',
                'rubrik' => [
                    4 => 'Penyusunan melibatkan seluruh pemangku kepentingan secara sangat partisipatif, disosialisasikan secara terstruktur, dan dipahami sangat mendalam oleh sivitas.',
                    3 => 'Penyusunan melibatkan pemangku kepentingan, disosialisasikan secara efektif, dan dipahami dengan baik.',
                    2 => 'Penyusunan cukup melibatkan pemangku kepentingan, sosialisasi dilakukan namun tingkat pemahaman belum merata.',
                    1 => 'Penyusunan bersifat sepihak dan sosialisasi tidak berjalan efektif.',
                ],
            ],
            [
                'criterion_key' => 'K1',
                'code' => 'BANPT-ELM-05-VMTS-STRATEGI-IKU',
                'node_code' => 'BANPT-NODE-ELM-05',
                'no_urut' => 5,
                'no_butir' => '1.3',
                'jenis' => 'O',
                'weight' => 6.0,
                'title' => 'Ketercapaian Indikator Kinerja Utama (IKU) dan Indikator Kinerja Tambahan (IKT)',
                'deskriptor' => 'Efektivitas strategi pencapaian target IKU dan IKT yang ditetapkan dalam Renstra dan rencana operasional (Renop).',
                'rubrik' => [
                    4 => 'Strategi pencapaian target IKU dan IKT berhasil melampaui standar nasional secara konsisten disertai evaluasi keberkalaan yang sangat komprehensif.',
                    3 => 'Target IKU dan IKT tercapai sesuai target Renstra dengan bukti ketercapaian yang sahih.',
                    2 => 'Sebagian besar target IKU tercapai namun IKT belum berkembang secara optimal.',
                    1 => 'Banyak target IKU yang belum tercapai dan strategi tidak berjalan efektif.',
                ],
            ],

            // Kriteria 2: Tata Pamong, Tata Kelola, dan Kerjasama (Bobot: 50.0)
            [
                'criterion_key' => 'K2',
                'code' => 'BANPT-ELM-06-TATA-PAMONG-SISTEM',
                'node_code' => 'BANPT-NODE-ELM-06',
                'no_urut' => 6,
                'no_butir' => '2.1.A',
                'jenis' => 'I',
                'weight' => 10.0,
                'title' => 'Sistem Tata Pamong dan Kepemimpinan Otonom & Kredibel',
                'deskriptor' => 'Keberfungsian sistem tata pamong yang mencakup 5 pilar tata kelola: kredibel, transparan, akuntabel, bertanggung jawab, dan adil (*good university governance*).',
                'syarat_perlu_peringkat' => 'Implementasi Good University Governance',
                'rubrik' => [
                    4 => 'Sistem tata pamong berjalan sangat efektif, otonom, didukung kepemimpinan operasional, organisasional, dan publik yang visioner serta berintegritas tinggi.',
                    3 => 'Sistem tata pamong berjalan efektif, transparan, akuntabel, dan didukung kepemimpinan yang baik.',
                    2 => 'Sistem tata pamong cukup berjalan namun efektivitas koordinasi antar unit masih perlu ditingkatkan.',
                    1 => 'Sistem tata pamong belum berjalan dengan baik dan tata kelola kurang transparan.',
                ],
            ],
            [
                'criterion_key' => 'K2',
                'code' => 'BANPT-ELM-07-SPMI-PPEPP',
                'node_code' => 'BANPT-NODE-ELM-07',
                'no_urut' => 7,
                'no_butir' => '2.2.A',
                'jenis' => 'P',
                'weight' => 20.0,
                'title' => 'Sistem Penjaminan Mutu Internal (Siklus PPEPP & Audit Mutu Internal)',
                'deskriptor' => 'Ketersediaan dan efektivitas implementasi SPMI di perguruan tinggi yang mencakup siklus PPEPP, AMI berkala, RTM, dan tindak lanjut perbaikan (RTL).',
                'syarat_perlu_peringkat' => 'SPMI berjalan konsisten dan efektif siklus PPEPP',
                'rubrik' => [
                    4 => 'SPMI terlembagakan secara mandiri, siklus PPEPP berjalan konsisten, AMI terlaksana berkala dengan asesor bersertifikat, RTM rutin, dan RTL terbukti meningkatkan budaya mutu secara berkelanjutan.',
                    3 => 'SPMI berjalan efektif, siklus PPEPP terlaksana, AMI dan RTM dilakukan secara berkala dan terdokumentasi lengkap.',
                    2 => 'SPMI sudah dibentuk namun siklus PPEPP belum tuntas (terutama pada tahap pengendalian dan peningkatan).',
                    1 => 'SPMI belum berjalan secara fungsional dan dokumen mutu tidak lengkap.',
                ],
            ],
            [
                'criterion_key' => 'K2',
                'code' => 'BANPT-ELM-08-KERJASAMA-TRIDHARMA',
                'node_code' => 'BANPT-NODE-ELM-08',
                'no_urut' => 8,
                'no_butir' => '2.3.A',
                'jenis' => 'O',
                'weight' => 20.0,
                'title' => 'Kerjasama Strategis Tridharma (Nasional dan Internasional)',
                'deskriptor' => 'Kuantitas, kualitas, relevansi, dan kemanfaatan kerjasama di bidang pendidikan, penelitian, dan PkM dengan mitra nasional dan internasional bereputasi.',
                'rubrik' => [
                    4 => 'Kerjasama strategis tridharma sangat produktif, memiliki mitra internasional bereputasi, dan memberikan dampak nyata terhadap peningkatan kualitas institusi.',
                    3 => 'Kerjasama tridharma aktif berjalan, memiliki mitra nasional dan internasional yang relevan, dan memberikan manfaat bagi institusi.',
                    2 => 'Kerjasama sudah terjalin namun realisasi kegiatan dan evaluasi kepuasan mitra masih terbatas.',
                    1 => 'Kerjasama sangat minim dan tidak aktif ditindaklanjuti.',
                ],
            ],

            // Kriteria 3: Mahasiswa (Bobot: 30.0)
            [
                'criterion_key' => 'K3',
                'code' => 'BANPT-ELM-09-MAHASISWA-PMB',
                'node_code' => 'BANPT-NODE-ELM-09',
                'no_urut' => 9,
                'no_butir' => '3.1',
                'jenis' => 'I',
                'weight' => 10.0,
                'title' => 'Sistem Rekrutmen dan Seleksi Mahasiswa Baru',
                'deskriptor' => 'Efektivitas sistem rekrutmen, animo pendaftar, selektivitas, serta program beasiswa dan afirmasi bagi mahasiswa berkebutuhan khusus/daerah 3T.',
                'rubrik' => [
                    4 => 'Sistem seleksi sangat transparan, rasio pendaftar terhadap yang diterima sangat kompetitif, dan memiliki program afirmasi/inklusif yang sangat kuat.',
                    3 => 'Sistem seleksi transparan, selektivitas baik, dan memiliki kebijakan afirmasi yang jelas.',
                    2 => 'Sistem seleksi berjalan normal namun animo dan selektivitas masih perlu ditingkatkan.',
                    1 => 'Sistem seleksi kurang transparan dan daya tampung tidak terpenuhi.',
                ],
            ],
            [
                'criterion_key' => 'K3',
                'code' => 'BANPT-ELM-10-MAHASISWA-LAYANAN',
                'node_code' => 'BANPT-NODE-ELM-10',
                'no_urut' => 10,
                'no_butir' => '3.2',
                'jenis' => 'P',
                'weight' => 10.0,
                'title' => 'Layanan dan Pembinaan Kemahasiswaan',
                'deskriptor' => 'Kualitas layanan minat, bakat, penalaran, kesejahteraan, konseling, bimbingan karir, dan pembinaan kewirausahaan mahasiswa.',
                'rubrik' => [
                    4 => 'Layanan kemahasiswaan sangat lengkap, modern, mudah diakses, serta berhasil menumbuhkan ekosistem prestasi dan kewirausahaan yang unggul.',
                    3 => 'Layanan kemahasiswaan lengkap dan berjalan secara efektif mendukung kegiatan mahasiswa.',
                    2 => 'Layanan kemahasiswaan tersedia namun belum dimanfaatkan secara optimal oleh mahasiswa.',
                    1 => 'Layanan kemahasiswaan sangat terbatas dan kurang memadai.',
                ],
            ],
            [
                'criterion_key' => 'K3',
                'code' => 'BANPT-ELM-11-MAHASISWA-PRESTASI',
                'node_code' => 'BANPT-NODE-ELM-11',
                'no_urut' => 11,
                'no_butir' => '3.3',
                'jenis' => 'O',
                'weight' => 10.0,
                'title' => 'Prestasi Mahasiswa di Tingkat Wilayah, Nasional, dan Internasional',
                'deskriptor' => 'Rasio dan jumlah perolehan prestasi mahasiswa di bidang akademik dan non-akademik di tingkat wilayah, nasional, dan internasional.',
                'rubrik' => [
                    4 => 'Prestasi mahasiswa sangat membanggakan di tingkat nasional dan internasional pada berbagai ajang kompetisi bergengsi.',
                    3 => 'Banyak perolehan prestasi mahasiswa di tingkat wilayah dan nasional serta beberapa di tingkat internasional.',
                    2 => 'Prestasi mahasiswa didominasi tingkat wilayah/lokal.',
                    1 => 'Prestasi mahasiswa sangat minim.',
                ],
            ],

            // Kriteria 4: Sumber Daya Manusia (Bobot: 60.0)
            [
                'criterion_key' => 'K4',
                'code' => 'BANPT-ELM-12-SDM-KUALIFIKASI-DOSEN',
                'node_code' => 'BANPT-NODE-ELM-12',
                'no_urut' => 12,
                'no_butir' => '4.1',
                'jenis' => 'I',
                'weight' => 20.0,
                'title' => 'Kecukupan, Rasio, dan Kualifikasi Akademik Dosen Tetap (S3/Doktor)',
                'deskriptor' => 'Persentase dosen tetap yang berkualifikasi akademik Doktor (S3), kecukupan dosen per program studi, dan kesesuaian rasio dosen:mahasiswa.',
                'syarat_perlu_peringkat' => 'Persentase dosen S3 dan rasio dosen mahasiswa',
                'rubrik' => [
                    4 => 'Kualifikasi dosen S3 > 50%, rasio dosen terhadap mahasiswa sangat ideal (1:15 s/d 1:30), dan seluruh dosen memiliki sertifikat pendidik profesional.',
                    3 => 'Kualifikasi dosen S3 antara 30% - 50%, rasio dosen:mahasiswa sesuai standar SN-Dikti.',
                    2 => 'Kualifikasi dosen S3 antara 15% - 30%, rasio dosen:mahasiswa mendekati ambang batas maksimum.',
                    1 => 'Kualifikasi dosen S3 < 15% atau rasio dosen terhadap mahasiswa melampaui batas yang diizinkan.',
                ],
            ],
            [
                'criterion_key' => 'K4',
                'code' => 'BANPT-ELM-13-SDM-JABATAN-FUNGSIONAL',
                'node_code' => 'BANPT-NODE-ELM-13',
                'no_urut' => 13,
                'no_butir' => '4.2',
                'jenis' => 'I',
                'weight' => 15.0,
                'title' => 'Jabatan Fungsional Akademik Dosen (Guru Besar & Lektor Kepala)',
                'deskriptor' => 'Persentase dosen tetap yang memiliki jabatan fungsional Guru Besar dan Lektor Kepala terhadap total dosen tetap.',
                'syarat_perlu_peringkat' => 'Persentase Guru Besar dan Lektor Kepala',
                'rubrik' => [
                    4 => 'Persentase Guru Besar dan Lektor Kepala > 40% dari total dosen tetap, menunjukkan kepakaran akademik yang sangat matang.',
                    3 => 'Persentase Guru Besar dan Lektor Kepala antara 25% - 40%.',
                    2 => 'Persentase Guru Besar dan Lektor Kepala antara 10% - 25%.',
                    1 => 'Persentase Guru Besar dan Lektor Kepala < 10%.',
                ],
            ],
            [
                'criterion_key' => 'K4',
                'code' => 'BANPT-ELM-14-SDM-TENDIK',
                'node_code' => 'BANPT-NODE-ELM-14',
                'no_urut' => 14,
                'no_butir' => '4.3',
                'jenis' => 'I',
                'weight' => 10.0,
                'title' => 'Kualifikasi, Kompetensi, dan Sertifikasi Tenaga Kependidikan',
                'deskriptor' => 'Kecukupan, kualifikasi, dan sertifikasi kompetensi tenaga kependidikan (pustakawan, laboran, teknisi, IT support, dan administrasi).',
                'rubrik' => [
                    4 => 'Tenaga kependidikan sangat mencukupi, berkualifikasi sesuai bidangnya, dan mayoritas bersertifikat kompetensi profesi.',
                    3 => 'Tenaga kependidikan mencukupi dan memiliki kualifikasi yang relevan dengan tugasnya.',
                    2 => 'Jumlah dan kualifikasi tenaga kependidikan cukup memadai namun sertifikasi kompetensi masih terbatas.',
                    1 => 'Tenaga kependidikan kurang memadai untuk mendukung operasional tridharma.',
                ],
            ],
            [
                'criterion_key' => 'K4',
                'code' => 'BANPT-ELM-15-SDM-PENGEMBANGAN',
                'node_code' => 'BANPT-NODE-ELM-15',
                'no_urut' => 15,
                'no_butir' => '4.4',
                'jenis' => 'P',
                'weight' => 15.0,
                'title' => 'Sistem Rekrutmen, Pengembangan Karir, Remunerasi, dan Beban Kerja SDM',
                'deskriptor' => 'Efektivitas sistem remunerasi berbasis kinerja, program beasiswa studi lanjut, pelatihan kompetensi berkala, dan monitoring evaluasi BKD/SKP.',
                'rubrik' => [
                    4 => 'Sistem pengelolaan SDM sangat komprehensif, berbasis meritokrasi, remunerasi berkeadilan, dan anggaran pengembangan SDM sangat memadai.',
                    3 => 'Sistem pengelolaan SDM baik, pengembangan karir terencana, dan evaluasi kinerja berjalan rutin.',
                    2 => 'Pengelolaan SDM cukup baik namun program studi lanjut dan skema insentif belum merata.',
                    1 => 'Pengelolaan SDM belum terstruktur dan pembinaan karir tidak berjalan.',
                ],
            ],

            // Kriteria 5: Keuangan, Sarana, dan Prasarana (Bobot: 40.0)
            [
                'criterion_key' => 'K5',
                'code' => 'BANPT-ELM-16-KEUANGAN-ALOKASI',
                'node_code' => 'BANPT-NODE-ELM-16',
                'no_urut' => 16,
                'no_butir' => '5.1',
                'jenis' => 'I',
                'weight' => 15.0,
                'title' => 'Kecukupan, Alokasi Anggaran Tridharma, dan Akuntabilitas Keuangan',
                'deskriptor' => 'Kecukupan dana operasional per mahasiswa, alokasi dana penelitian & PkM, keberlanjutan pendanaan (*financial sustainability*), dan opini audit eksternal.',
                'rubrik' => [
                    4 => 'Biaya operasional per mahasiswa sangat memadai, alokasi dana tridharma tinggi, memiliki dana abadi/sumber pendapatan non-SPP yang kuat, dan opini audit WTP.',
                    3 => 'Pendanaan stabil, alokasi tridharma sesuai standar, dan laporan keuangan diaudit secara independen.',
                    2 => 'Pendanaan bergantung penuh pada SPP mahasiswa namun operasional tetap berjalan tertib.',
                    1 => 'Pendanaan defisit dan mengganggu kelancaran kegiatan tridharma.',
                ],
            ],
            [
                'criterion_key' => 'K5',
                'code' => 'BANPT-ELM-17-SARPRAS-FASILITAS',
                'node_code' => 'BANPT-NODE-ELM-17',
                'no_urut' => 17,
                'no_butir' => '5.2',
                'jenis' => 'I',
                'weight' => 15.0,
                'title' => 'Kecukupan, Kelayakan, dan Pemeliharaan Sarana & Prasarana Kampus',
                'deskriptor' => 'Kelayakan ruang kuliah, laboratorium mutakhir, perpustakaan, sarana olahraga/seni, aksesibilitas disabilitas, dan fasilitas ramah lingkungan (*green campus*).',
                'rubrik' => [
                    4 => 'Sarana prasarana sangat modern, laboratorium terakreditasi/bersertifikasi, perpustakaan digital lengkap, akses disabilitas memadai, dan terawat prima.',
                    3 => 'Sarana prasarana lengkap, laboratorium berfungsi baik, dan lingkungan kampus kondusif.',
                    2 => 'Sarana prasarana cukup memadai untuk pembelajaran standar.',
                    1 => 'Sarana prasarana sangat terbatas dan laboratorium tidak memadai.',
                ],
            ],
            [
                'criterion_key' => 'K5',
                'code' => 'BANPT-ELM-18-SISTEM-INFORMASI-TIK',
                'node_code' => 'BANPT-NODE-ELM-18',
                'no_urut' => 18,
                'no_butir' => '5.3',
                'jenis' => 'P',
                'weight' => 10.0,
                'title' => 'Sistem Informasi Terintegrasi dan Tata Kelola TIK Kampus',
                'deskriptor' => 'Ketersediaan sistem informasi terintegrasi (SIAKAD, SPMI, Keuangan, SDM, E-Learning/LMS, Repository) dan keamanan siber.',
                'rubrik' => [
                    4 => 'Sistem informasi terintegrasi penuh (*single sign-on*), didukung infrastruktur cloud/server handal, keamanan data terjamin, dan analitik data cerdas.',
                    3 => 'Sistem informasi terintegrasi dengan baik dan mendukung layanan akademik/non-akademik secara lancar.',
                    2 => 'Sistem informasi tersedia namun masih ada modul yang parsial/belum terhubung penuh.',
                    1 => 'Sistem informasi belum terintegrasi dan layanan dilakukan manual.',
                ],
            ],

            // Kriteria 6: Pendidikan (Bobot: 70.0)
            [
                'criterion_key' => 'K6',
                'code' => 'BANPT-ELM-19-PENDIDIKAN-KURIKULUM-OBE',
                'node_code' => 'BANPT-NODE-ELM-19',
                'no_urut' => 19,
                'no_butir' => '6.1',
                'jenis' => 'I',
                'weight' => 25.0,
                'title' => 'Kurikulum Berbasis Capaian Pembelajaran (OBE) & Fleksibilitas MBKM',
                'deskriptor' => 'Penyusunan kurikulum berbasis Outcome-Based Education (OBE), kesesuaian dengan KKNI dan SN-Dikti, keterlibatan DUDIKA, dan implementasi pembelajaran merdeka (MBKM/magang/pertukaran pelajar).',
                'syarat_perlu_peringkat' => 'Implementasi kurikulum OBE dan MBKM',
                'rubrik' => [
                    4 => 'Kurikulum seluruh prodi berbasis OBE secara komprehensif, terakreditasi internasional/nasional unggul, dan implementasi program MBKM berjalan sangat masif dan bermutu tinggi.',
                    3 => 'Kurikulum berbasis OBE, sesuai KKNI/SN-Dikti, dan program MBKM terfasilitasi dengan baik.',
                    2 => 'Kurikulum sedang dalam transisi menuju OBE dan program MBKM berjalan pada sebagian prodi.',
                    1 => 'Kurikulum belum menerapkan OBE dan tidak adaptif terhadap perkembangan iptek.',
                ],
            ],
            [
                'criterion_key' => 'K6',
                'code' => 'BANPT-ELM-20-PENDIDIKAN-PROSES-PEMBELAJARAN',
                'node_code' => 'BANPT-NODE-ELM-20',
                'no_urut' => 20,
                'no_butir' => '6.2',
                'jenis' => 'P',
                'weight' => 25.0,
                'title' => 'Pelaksanaan Pembelajaran Interaktif, Suasana Akademik & Metode Student-Centered',
                'deskriptor' => 'Metode pembelajaran aktif (CBL, PBL, flipped classroom, micro-credential), pemanfaatan LMS modern, dan penciptaan suasana akademik yang dinamis.',
                'rubrik' => [
                    4 => 'Metode pembelajaran berpusat pada mahasiswa (PBL/CBL) diterapkan menyeluruh, integrasi teknologi dan riset dosen ke perkuliahan sangat kuat, dan interaksi akademik sangat produktif.',
                    3 => 'Pembelajaran aktif terlaksana dengan baik didukung modul digital dan suasana akademik yang kondusif.',
                    2 => 'Metode pembelajaran masih dominan ceramah/konvensional pada beberapa mata kuliah.',
                    1 => 'Proses pembelajaran tidak termonitor dengan baik dan suasana akademik pasif.',
                ],
            ],
            [
                'criterion_key' => 'K6',
                'code' => 'BANPT-ELM-21-PENDIDIKAN-MONEV-PENILAIAN',
                'node_code' => 'BANPT-NODE-ELM-21',
                'no_urut' => 21,
                'no_butir' => '6.3',
                'jenis' => 'O',
                'weight' => 20.0,
                'title' => 'Sistem Monitoring, Evaluasi Pembelajaran & Penilaian Ketercapaian CPL',
                'deskriptor' => 'Keberkalaan survei kepuasan mahasiswa, evaluasi dosen oleh mahasiswa (EDOM), audit RPS, dan asesmen pengukuran portofolio CPL mahasiswa.',
                'rubrik' => [
                    4 => 'Sistem monitoring dan evaluasi CPL terotomatisasi secara digital, tindak lanjut EDOM transparan, dan tingkat kepuasan mahasiswa sangat tinggi (>90%).',
                    3 => 'Monitoring dan evaluasi pembelajaran berjalan rutin dan ditindaklanjuti untuk perbaikan semester berikutnya.',
                    2 => 'Monev dilaksanakan namun pengukuran ketercapaian CPL belum terstruktur rapi.',
                    1 => 'Monev pembelajaran tidak dilakukan secara konsisten.',
                ],
            ],

            // Kriteria 7: Penelitian (Bobot: 40.0)
            [
                'criterion_key' => 'K7',
                'code' => 'BANPT-ELM-22-PENELITIAN-ROADMAP',
                'node_code' => 'BANPT-NODE-ELM-22',
                'no_urut' => 22,
                'no_butir' => '7.1',
                'jenis' => 'I',
                'weight' => 10.0,
                'title' => 'Peta Jalan (Roadmap) Penelitian dan Kelompok Riset Institusi',
                'deskriptor' => 'Kejelasan roadmap riset institusi, fokus keunggulan riset (*research niche*), dan pembinaan kelompok riset interdisiplin.',
                'rubrik' => [
                    4 => 'Roadmap penelitian sangat visioner, memiliki keunggulan riset yang diakui nasional/internasional, dan didukung pusat studi/lab riset unggulan.',
                    3 => 'Roadmap penelitian jelas dan menjadi panduan bagi seluruh riset dosen dan mahasiswa.',
                    2 => 'Roadmap penelitian ada namun penerapannya pada level dosen belum seragam.',
                    1 => 'Roadmap penelitian tidak terdefinisi dengan jelas.',
                ],
            ],
            [
                'criterion_key' => 'K7',
                'code' => 'BANPT-ELM-23-PENELITIAN-DANA-PELAKSANAAN',
                'node_code' => 'BANPT-NODE-ELM-23',
                'no_urut' => 23,
                'no_butir' => '7.2',
                'jenis' => 'P',
                'weight' => 15.0,
                'title' => 'Perolehan Dana Hibah, Kerjasama Riset, dan Pelibatan Mahasiswa',
                'deskriptor' => 'Jumlah dan rata-rata dana hibah riset (DRTPM, LPDP, industri, internasional) dan persentase pelibatan mahasiswa dalam riset dosen.',
                'rubrik' => [
                    4 => 'Rata-rata perolehan dana hibah kompetitif dan industri sangat tinggi (>Rp 20 juta/dosen/tahun) dan >40% mahasiswa terlibat langsung dalam riset.',
                    3 => 'Perolehan dana hibah penelitian memadai dan pelibatan mahasiswa berjalan baik.',
                    2 => 'Dana riset masih didominasi dana internal mandiri institusi.',
                    1 => 'Aktivitas dan pendanaan riset sangat minim.',
                ],
            ],
            [
                'criterion_key' => 'K7',
                'code' => 'BANPT-ELM-24-PENELITIAN-INTEGRASI',
                'node_code' => 'BANPT-NODE-ELM-24',
                'no_urut' => 24,
                'no_butir' => '7.3',
                'jenis' => 'O',
                'weight' => 15.0,
                'title' => 'Integrasi Hasil Riset ke dalam Pembelajaran dan Solusi Industri',
                'deskriptor' => 'Pemanfaatan hasil penelitian dosen sebagai bahan ajar, buku teks, modul praktikum, dan teknologi tepat guna bagi industri/masyarakat.',
                'rubrik' => [
                    4 => 'Hasil riset terintegrasi masif ke dalam buku ajar ber-ISBN, modul pembelajaran mutakhir, dan diadopsi luas oleh industri.',
                    3 => 'Hasil riset digunakan secara berkala dalam pembaruan materi perkuliahan.',
                    2 => 'Integrasi hasil riset ke pembelajaran masih terbatas pada inisiatif beberapa dosen.',
                    1 => 'Hasil riset tidak diintegrasikan ke proses pendidikan.',
                ],
            ],

            // Kriteria 8: Pengabdian kepada Masyarakat (Bobot: 30.0)
            [
                'criterion_key' => 'K8',
                'code' => 'BANPT-ELM-25-PKM-ROADMAP',
                'node_code' => 'BANPT-NODE-ELM-25',
                'no_urut' => 25,
                'no_butir' => '8.1',
                'jenis' => 'I',
                'weight' => 8.0,
                'title' => 'Peta Jalan (Roadmap) PkM dan Layanan Kepakaran',
                'deskriptor' => 'Keberadaan peta jalan PkM yang berfokus pada pemberdayaan masyarakat, penerapan sains/teknologi, dan penyelesaian masalah sosial-ekonomi.',
                'rubrik' => [
                    4 => 'Roadmap PkM terarah sangat jelas, menghasilkan desa/wilayah binaan terpadu, dan menjadi rujukan pemberdayaan masyarakat nasional.',
                    3 => 'Roadmap PkM terstruktur baik dan selaras dengan fokus keilmuan institusi.',
                    2 => 'Roadmap PkM ada namun program PkM masih bersifat sporadis/reaktif.',
                    1 => 'Roadmap PkM tidak terencana dengan baik.',
                ],
            ],
            [
                'criterion_key' => 'K8',
                'code' => 'BANPT-ELM-26-PKM-PELAKSANAAN-DANA',
                'node_code' => 'BANPT-NODE-ELM-26',
                'no_urut' => 26,
                'no_butir' => '8.2',
                'jenis' => 'P',
                'weight' => 12.0,
                'title' => 'Pelaksanaan Kegiatan PkM, Pendanaan Eksternal & Pelibatan Mahasiswa',
                'deskriptor' => 'Frekuensi kegiatan PkM, perolehan dana hibah PkM eksternal/CSR, dan kolaborasi aktif dengan mahasiswa dalam pengabdian.',
                'rubrik' => [
                    4 => 'Kegiatan PkM didanai kuat oleh hibah nasional/internasional/CSR, melibatkan mahasiswa secara luas, dan berkesinambungan.',
                    3 => 'Kegiatan PkM terlaksana rutin dengan pendanaan yang layak dan pelibatan mahasiswa yang baik.',
                    2 => 'Kegiatan PkM terlaksana namun pendanaan masih bergantung penuh pada dana internal kecil.',
                    1 => 'Aktivitas PkM sangat rendah.',
                ],
            ],
            [
                'criterion_key' => 'K8',
                'code' => 'BANPT-ELM-27-PKM-DAMPAK-MASYARAKAT',
                'node_code' => 'BANPT-NODE-ELM-27',
                'no_urut' => 27,
                'no_butir' => '8.3',
                'jenis' => 'O',
                'weight' => 10.0,
                'title' => 'Dampak Nyata, Kepuasan Mitra, dan Keberlanjutan Program PkM',
                'deskriptor' => 'Tingkat kepuasan mitra masyarakat, perubahan taraf hidup/kemandirian mitra sasaran, dan keberlanjutan hasil karya pengabdian.',
                'rubrik' => [
                    4 => 'Program PkM memberikan dampak transformatif nyata (peningkatan ekonomi/kesehatan/literasi), mendapat apresiasi tinggi mitra, dan berkelanjutan.',
                    3 => 'Program PkM memberikan manfaat langsung dan mitra menyatakan sangat puas.',
                    2 => 'Program PkM cukup bermanfaat namun tindak lanjut pasca kegiatan kurang terpantau.',
                    1 => 'Program PkM tidak memberikan dampak yang jelas.',
                ],
            ],

            // Kriteria 9: Luaran dan Capaian Tridharma (Bobot: 40.0)
            [
                'criterion_key' => 'K9',
                'code' => 'BANPT-ELM-28-LUARAN-LULUSAN-KARIR',
                'node_code' => 'BANPT-NODE-ELM-28',
                'no_urut' => 28,
                'no_butir' => '9.1',
                'jenis' => 'O',
                'weight' => 15.0,
                'title' => 'Kualitas Lulusan, Waktu Tunggu, Keselarasan Kerja & Tracer Study',
                'deskriptor' => 'Rata-rata IPK lulusan, ketepatan masa studi, waktu tunggu kerja (< 6 bulan), kesesuaian bidang kerja, dan tingkat respon survei tracer study.',
                'syarat_perlu_peringkat' => 'Ketepatan kelulusan dan waktu tunggu kerja lulusan',
                'rubrik' => [
                    4 => 'Waktu tunggu lulusan < 3 bulan, kesesuaian bidang kerja > 80%, tingkat kepuasan pengguna lulusan sangat tinggi, dan tracer study terlaksana sistematis.',
                    3 => 'Waktu tunggu lulusan < 6 bulan, kesesuaian kerja > 65%, dan tracer study berjalan baik.',
                    2 => 'Waktu tunggu lulusan antara 6 - 12 bulan.',
                    1 => 'Waktu tunggu lulusan > 12 bulan dan pelacakan jejak alumni tidak terdokumentasi.',
                ],
            ],
            [
                'criterion_key' => 'K9',
                'code' => 'BANPT-ELM-29-LUARAN-PUBLIKASI-BEREPUTASI',
                'node_code' => 'BANPT-NODE-ELM-29',
                'no_urut' => 29,
                'no_butir' => '9.2',
                'jenis' => 'O',
                'weight' => 15.0,
                'title' => 'Publikasi Ilmiah Bereputasi Internasional (Scopus/WoS) & Nasional (SINTA)',
                'deskriptor' => 'Rasio dan jumlah publikasi artikel ilmiah pada jurnal internasional bereputasi, jurnal nasional terakreditasi SINTA 1-2, sitasi ilmiah, dan h-index institusi.',
                'syarat_perlu_peringkat' => 'Publikasi ilmiah dosen pada jurnal bereputasi',
                'rubrik' => [
                    4 => 'Rasio publikasi internasional bereputasi dan SINTA 1-2 > 1.0 artikel/dosen/tahun, angka sitasi sangat tinggi, dan reputasi ilmiah institusi terkemuka.',
                    3 => 'Publikasi ilmiah terakreditasi dan bereputasi stabil serta produktif sesuai target standar mutu.',
                    2 => 'Publikasi didominasi jurnal nasional SINTA 3-6 atau prosiding lokal.',
                    1 => 'Publikasi ilmiah sangat minim (< 0.2 artikel/dosen/tahun).',
                ],
            ],
            [
                'criterion_key' => 'K9',
                'code' => 'BANPT-ELM-30-LUARAN-HKI-PATEN',
                'node_code' => 'BANPT-NODE-ELM-30',
                'no_urut' => 30,
                'no_butir' => '9.3',
                'jenis' => 'O',
                'weight' => 10.0,
                'title' => 'Perolehan Hak Kekayaan Intelektual (Paten, Hak Cipta) dan Produk Komersial',
                'deskriptor' => 'Jumlah perolehan paten (granted), paten sederhana, hak cipta, desain industri, serta hilirisasi produk inovasi ke masyarakat/industri.',
                'rubrik' => [
                    4 => 'Perguruan tinggi memiliki banyak paten granted, produk inovasi berhasil dikomersialisasikan ke industri, dan royalti HKI mengalir ke institusi.',
                    3 => 'Perolehan HKI dan hak cipta terdaftar aktif dalam jumlah signifikan.',
                    2 => 'Perolehan HKI cukup ada namun sebatas hak cipta karya tulis/software sederhana.',
                    1 => 'Tidak ada perolehan HKI atau paten yang tercatat.',
                ],
            ],

            // Analisis dan Program Pengembangan Institusi (Bobot: 12.0)
            [
                'criterion_key' => 'PENGEMBANGAN',
                'code' => 'BANPT-ELM-31-ANALISIS-SWOT-PENGEMBANGAN',
                'node_code' => 'BANPT-NODE-ELM-31',
                'no_urut' => 31,
                'no_butir' => '10.1',
                'jenis' => 'O',
                'weight' => 12.0,
                'title' => 'Analisis Capaian Kinerja, Strategi Keberlanjutan & Rencana Pengembangan',
                'deskriptor' => 'Kedalaman evaluasi diri institusi, pemetaan akar masalah (*root cause analysis*), dan perumusan program pengembangan jangka panjang yang visioner.',
                'rubrik' => [
                    4 => 'Evaluasi diri sangat jujur, analitis, berbasis data akurat, dan program pengembangan strategis disusun sangat realistis untuk mencapai daya saing global.',
                    3 => 'Evaluasi diri komprehensif dan rencana pengembangan institusi terarah dengan baik.',
                    2 => 'Evaluasi diri cukup baik namun analisis akar masalah belum mendalam.',
                    1 => 'Evaluasi diri bersifat deskriptif tanpa analisis perbaikan yang terencana.',
                ],
            ],
        ];
    }
}
