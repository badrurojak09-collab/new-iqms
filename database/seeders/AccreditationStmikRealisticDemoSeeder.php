<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accreditation;
use App\Models\AccreditationAssessment;
use App\Models\AccreditationBody;
use App\Models\AccreditationCriterion;
use App\Models\AccreditationReadinessItem;
use App\Models\AccreditationResponse;
use App\Models\AccreditationScoreSnapshot;
use App\Models\AccreditationSection;
use App\Models\AccreditationSubmission;
use App\Models\AssessmentCriterion;
use App\Models\AssessmentElement;
use App\Models\AssessmentIndicator;
use App\Models\Evidence;
use App\Models\EvidenceCollection;
use App\Models\EvidenceCollectionItem;
use App\Models\EvidenceLink;
use App\Models\InstrumentFamily;
use App\Models\InstrumentMapping;
use App\Models\InstrumentNode;
use App\Models\InstrumentScoringRule;
use App\Models\InstrumentVersion;
use App\Models\LedTemplate;
use App\Models\LedTemplateSection;
use App\Models\LkpsTemplate;
use App\Models\LkpsTemplateColumn;
use App\Models\ProgramStudi;
use App\Models\PerguruanTinggi;
use App\Models\ReadinessGap;
use App\Models\ReadinessResult;
use App\Models\ReadinessRun;
use App\Models\SpmiIndicator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class AccreditationStmikRealisticDemoSeeder extends Seeder
{
    private const COLLEGE_CODE = 'STMIK-NUSANTARA-DEMO';
    private const VERSION_LABEL = 'LAM INFOKOM 2.1 - 2025 - Sarjana';
    private const VERSION_SOURCE = 'Tiga dokumen matriks LAM INFOKOM yang disediakan untuk konfigurasi demo; angka operasional bersifat sintetis agregat.';

    /** @var array<int, array{code:string,name:string,description:string}> */
    private const CRITERIA = [
        ['code' => 'KR-BM', 'name' => 'Budaya Mutu', 'description' => 'Efektivitas pelaksanaan sistem penjaminan mutu internal melalui siklus PPEPP.'],
        ['code' => 'KR-RP', 'name' => 'Relevansi Pendidikan', 'description' => 'Kecukupan sumber daya, proses pembelajaran, dan capaian lulusan.'],
        ['code' => 'KR-RPEN', 'name' => 'Relevansi Penelitian dan Pengabdian kepada Masyarakat', 'description' => 'Kinerja penelitian dan pengabdian yang relevan dengan bidang keilmuan.'],
        ['code' => 'KR-AKT', 'name' => 'Akuntabilitas, Struktur Organisasi, dan Tata Kelola', 'description' => 'Kelengkapan tata kelola, struktur organisasi, dan akuntabilitas institusi.'],
    ];

    /** @var array<int, array{code:string,criterion:string,node:string,title:string,weight:float,indicator:string}> */
    private const ELEMENTS = [
        ['code' => 'E-BM-01', 'criterion' => 'KR-BM', 'node' => 'E-BM-01', 'title' => 'Efektivitas SPMI: Penetapan standar', 'weight' => 10.0, 'indicator' => 'IND-19'],
        ['code' => 'E-BM-02', 'criterion' => 'KR-BM', 'node' => 'E-BM-02', 'title' => 'Efektivitas SPMI: Pelaksanaan standar', 'weight' => 10.0, 'indicator' => 'IND-19'],
        ['code' => 'E-BM-03', 'criterion' => 'KR-BM', 'node' => 'E-BM-03', 'title' => 'Efektivitas SPMI: Evaluasi', 'weight' => 10.0, 'indicator' => 'IND-20'],
        ['code' => 'E-BM-04', 'criterion' => 'KR-BM', 'node' => 'E-BM-04', 'title' => 'Efektivitas SPMI: Pengendalian', 'weight' => 10.0, 'indicator' => 'IND-20'],
        ['code' => 'E-BM-05', 'criterion' => 'KR-BM', 'node' => 'E-BM-05', 'title' => 'Efektivitas SPMI: Peningkatan', 'weight' => 10.0, 'indicator' => 'IND-20'],
        ['code' => 'E-RP-01', 'criterion' => 'KR-RP', 'node' => 'E-RP-01', 'title' => 'Jumlah dosen tetap homebase', 'weight' => 8.0, 'indicator' => 'IND-07'],
        ['code' => 'E-RP-02', 'criterion' => 'KR-RP', 'node' => 'E-RP-02', 'title' => 'Rasio jumlah dosen terhadap mahasiswa', 'weight' => 8.0, 'indicator' => 'IND-07'],
        ['code' => 'E-RP-03', 'criterion' => 'KR-RP', 'node' => 'E-RP-03', 'title' => 'Persentase kualifikasi dosen', 'weight' => 8.0, 'indicator' => 'IND-08'],
        ['code' => 'E-RP-04', 'criterion' => 'KR-RP', 'node' => 'E-RP-04', 'title' => 'Jumlah dosen dengan jabatan akademik', 'weight' => 8.0, 'indicator' => 'IND-09'],
        ['code' => 'E-RL-01', 'criterion' => 'KR-RP', 'node' => 'E-RL-01', 'title' => 'Persentase lulusan', 'weight' => 8.0, 'indicator' => 'IND-01'],
        ['code' => 'E-RL-02', 'criterion' => 'KR-RP', 'node' => 'E-RL-02', 'title' => 'Persentase kelulusan tepat waktu', 'weight' => 8.0, 'indicator' => 'IND-02'],
        ['code' => 'E-RPEN-01', 'criterion' => 'KR-RPEN', 'node' => 'E-RPEN-01', 'title' => 'Rasio judul penelitian terhadap dosen', 'weight' => 6.0, 'indicator' => 'IND-14'],
        ['code' => 'E-RPEN-02', 'criterion' => 'KR-RPEN', 'node' => 'E-RPEN-02', 'title' => 'Rasio publikasi terhadap dosen', 'weight' => 6.0, 'indicator' => 'IND-15'],
        ['code' => 'E-RPEN-03', 'criterion' => 'KR-RPEN', 'node' => 'E-RPEN-03', 'title' => 'Rasio kegiatan pengabdian kepada masyarakat', 'weight' => 6.0, 'indicator' => 'IND-16'],
        ['code' => 'E-AKT-01', 'criterion' => 'KR-AKT', 'node' => 'E-AKT-01', 'title' => 'Kelengkapan struktur organisasi dan tata kelola', 'weight' => 6.0, 'indicator' => 'IND-18'],
    ];

    /** @var array<int, array{code:string,title:string,dimension:string}> */
    private const DIMENSIONS = [
        ['code' => 'DIM-BUDAYA-MUTU', 'title' => 'Budaya Mutu', 'dimension' => 'Budaya Mutu'],
        ['code' => 'DIM-RELEVANSI-PENDIDIKAN', 'title' => 'Relevansi Pendidikan', 'dimension' => 'Relevansi Pendidikan'],
        ['code' => 'DIM-RELEVANSI-PENELITIAN', 'title' => 'Relevansi Penelitian dan Pengabdian kepada Masyarakat', 'dimension' => 'Relevansi Penelitian'],
        ['code' => 'DIM-AKUNTABILITAS', 'title' => 'Akuntabilitas, Struktur Organisasi, dan Tata Kelola', 'dimension' => 'Akuntabilitas'],
    ];

    public function run(): void
    {
        if (! ProgramStudi::query()->where('kode_prodi', 'S1-INFORMATIKA-DEMO')->exists()) {
            $this->call(SpmiStmikRealisticDemoSeeder::class);
        }

        if (! InstrumentVersion::query()->where('version_label', self::VERSION_LABEL)->exists()) {
            $this->call(LamInfokom21CriteriaSeeder::class);
        }

        DB::transaction(function (): void {
            $college = PerguruanTinggi::query()->where('kode_pt', self::COLLEGE_CODE)->firstOrFail();
            $actor = User::query()->where('perguruan_tinggi_id', $college->getKey())->first()
                ?? User::query()->firstOrFail();
            $version = $this->ensureInstrument($actor);
            $criteria = $this->ensureCriteria($version);
            $elements = $this->ensureElements($version, $criteria);
            $this->ensureScoringRule($version);
            $this->ensureTemplates($version, $elements);

            $programs = ProgramStudi::query()
                ->where('perguruan_tinggi_id', $college->getKey())
                ->whereIn('kode_prodi', ['S1-INFORMATIKA-DEMO', 'S1-SISTEM-INFORMASI-DEMO'])
                ->orderBy('kode_prodi')
                ->get();

            if ($programs->isEmpty()) {
                throw new RuntimeException('Program studi demo SPMI belum tersedia. Jalankan SpmiStmikRealisticDemoSeeder terlebih dahulu.');
            }

            foreach ($programs as $program) {
                $this->seedAccreditation($college, $program, $version, $criteria, $elements, $actor);
            }
        });
    }

    private function ensureInstrument(User $actor): InstrumentVersion
    {
        $body = AccreditationBody::query()->firstOrCreate(
            ['code' => 'LAM-INFOKOM'],
            ['name' => 'LAM INFOKOM', 'kind' => 'lam', 'website' => 'https://laminfokom.or.id', 'status' => 'active'],
        );

        $family = InstrumentFamily::query()->firstOrCreate(
            ['accreditation_body_id' => $body->getKey(), 'code' => 'LAM-INFOKOM-APS'],
            ['name' => 'Instrumen Akreditasi Program Studi LAM INFOKOM', 'scope_type' => 'program_study', 'description' => 'Keluarga instrumen demo berbasis LAM INFOKOM.'],
        );

        return InstrumentVersion::query()->firstOrCreate(
            ['instrument_family_id' => $family->getKey(), 'version_label' => self::VERSION_LABEL],
            [
                'status' => 'draft',
                'source_reference' => self::VERSION_SOURCE,
                'effective_from' => '2025-01-01',
                'changelog' => ['created_by_seeder' => true, 'note' => 'Baseline demo; wajib direview sebelum active.'],
                'content_hash' => hash('sha256', self::VERSION_LABEL . self::VERSION_SOURCE),
            ],
        );
    }

    /** @return array{accreditation: array<string, AccreditationCriterion>, assessment: array<string, AssessmentCriterion>} */
    private function ensureCriteria(InstrumentVersion $version): array
    {
        $accreditationCriteria = [];
        $assessmentCriteria = [];
        $dimensionCodes = [
            'KR-BM' => 'DIM-BUDAYA-MUTU',
            'KR-RP' => 'DIM-RELEVANSI-PENDIDIKAN',
            'KR-RPEN' => 'DIM-RELEVANSI-PENELITIAN',
            'KR-AKT' => 'DIM-AKUNTABILITAS',
        ];

        foreach (self::DIMENSIONS as $index => $dimension) {
            InstrumentNode::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $dimension['code']],
                ['node_type' => 'dimension', 'title' => $dimension['title'], 'sort_order' => $index + 1, 'is_required' => true, 'metadata' => ['source' => 'LAM INFOKOM baseline']],
            );
        }

        foreach (self::CRITERIA as $index => $definition) {
            $accreditationCriteria[$definition['code']] = AccreditationCriterion::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'sort_order' => $index + 1,
                    'is_required' => true,
                    'metadata' => ['source_type' => 'lam_infokom_public_baseline', 'data_provenance' => 'structured_from_user_documents'],
                ],
            );

            $assessmentCriteria[$definition['code']] = AssessmentCriterion::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['code']],
                [
                    'instrument_node_id' => InstrumentNode::query()
                        ->where('instrument_version_id', $version->getKey())
                        ->where('code', $dimensionCodes[$definition['code']])
                        ->value('id'),
                    'name' => $definition['name'],
                    'weight' => 25,
                    'minimum_score' => 2.5,
                    'sort_order' => $index + 1,
                    'is_required' => true,
                ],
            );
        }

        return ['accreditation' => $accreditationCriteria, 'assessment' => $assessmentCriteria];
    }

    /** @param array<string, AccreditationCriterion> $criteria @return array<string, array{node:InstrumentNode, element:AssessmentElement}> */
    private function ensureElements(InstrumentVersion $version, array $criteria): array
    {
        $dimensionNodes = [];
        foreach (self::DIMENSIONS as $index => $dimension) {
            $dimensionNodes[$dimension['code']] = InstrumentNode::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $dimension['code']],
                ['node_type' => 'dimension', 'title' => $dimension['title'], 'sort_order' => $index + 1, 'is_required' => true, 'metadata' => ['source' => 'LAM INFOKOM baseline']],
            );
        }

        $result = [];
        foreach (self::ELEMENTS as $index => $definition) {
            $dimensionCode = match ($definition['criterion']) {
                'KR-BM' => 'DIM-BUDAYA-MUTU',
                'KR-RP' => 'DIM-RELEVANSI-PENDIDIKAN',
                'KR-RPEN' => 'DIM-RELEVANSI-PENELITIAN',
                default => 'DIM-AKUNTABILITAS',
            };
            $node = InstrumentNode::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'code' => $definition['node']],
                [
                    'parent_id' => $dimensionNodes[$dimensionCode]->getKey(),
                    'node_type' => 'element',
                    'title' => $definition['title'],
                    'requirement' => 'Program studi menyediakan data, analisis, dan evidence yang dapat diverifikasi.',
                    'guidance' => 'Gunakan data periode terbaru dan jelaskan siklus PPEPP serta tindak lanjutnya.',
                    'weight' => $definition['weight'],
                    'sort_order' => $index + 1,
                    'is_required' => true,
                    'metadata' => ['source_type' => 'lam_infokom_public_baseline', 'data_provenance' => 'structured_from_user_documents'],
                ],
            );
            $element = AssessmentElement::query()->updateOrCreate(
                ['instrument_node_id' => $node->getKey(), 'code' => $definition['code']],
                [
                    'assessment_criterion_id' => $criteria['assessment'][$definition['criterion']]->getKey(),
                    'title' => $definition['title'],
                    'element_type' => 'quantitative_and_qualitative',
                    'weight' => $definition['weight'],
                    'is_required' => true,
                    'sort_order' => $index + 1,
                    'metadata' => ['source_indicator_code' => $definition['indicator'], 'data_provenance' => 'demo_mapping'],
                ],
            );
            AssessmentIndicator::query()->updateOrCreate(
                ['assessment_element_id' => $element->getKey(), 'code' => 'AI-' . $definition['code']],
                [
                    'name' => $definition['title'],
                    'unit' => str_contains(strtolower($definition['title']), 'rasio') ? 'rasio' : 'persen',
                    'direction' => 'higher_is_better',
                    'data_type' => 'decimal',
                    'target_definition' => ['source_spmi_indicator_code' => $definition['indicator'], 'data_provenance' => 'demo_mapping'],
                    'is_required' => true,
                    'sort_order' => $index + 1,
                ],
            );

            $result[$definition['code']] = ['node' => $node, 'element' => $element];
        }
        return $result;
    }

    private function ensureScoringRule(InstrumentVersion $version): void
    {
        InstrumentScoringRule::query()->updateOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => 'RULE-LAM-INFOKOM-UNGGUL-DEMO'],
            [
                'rule_type' => 'status_qualification',
                'expression' => [
                    'status' => 'unggul',
                    'operator' => 'and',
                    'conditions' => [
                        ['key' => 'score_total', 'operator' => '>=', 'value' => 3.5],
                        ['key' => 'budaya_mutu', 'operator' => '>=', 'value' => 3.5],
                        ['key' => 'relevansi_pendidikan', 'operator' => '>=', 'value' => 3.5],
                        ['key' => 'relevansi_penelitian', 'operator' => '>=', 'value' => 3.5],
                    ],
                ],
                'parameters' => ['source_type' => 'demo_baseline', 'requires_manual_validation' => true],
            ],
        );
    }

    /** @param array<string, array{node:InstrumentNode, element:AssessmentElement}> $elements */
    private function ensureTemplates(InstrumentVersion $version, array $elements): void
    {
        $led = LedTemplate::query()->updateOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => 'LED-LAM-INFOKOM-21-DEMO'],
            ['name' => 'Template LED LAM INFOKOM 2.1 Demo', 'description' => 'Template LED demo untuk empat kelompok kriteria.', 'validation_rules' => ['required_sections' => true]],
        );
        foreach (self::CRITERIA as $index => $criterion) {
            $element = $elements[array_values(array_filter(self::ELEMENTS, fn (array $item): bool => $item['criterion'] === $criterion['code']))[0]['code']]['node'];
            LedTemplateSection::query()->updateOrCreate(
                ['led_template_id' => $led->getKey(), 'code' => 'LED-' . $criterion['code']],
                ['instrument_node_id' => $element->getKey(), 'title' => $criterion['name'], 'guidance' => 'Jelaskan konteks, capaian, analisis, evidence, dan tindak lanjut.', 'is_required' => true, 'sort_order' => $index + 1, 'validation_rules' => ['min_characters' => 200]],
            );
        }

        $lkps = LkpsTemplate::query()->updateOrCreate(
            ['instrument_version_id' => $version->getKey(), 'code' => 'LKPS-LAM-INFOKOM-21-DEMO'],
            ['name' => 'Template LKPS LAM INFOKOM 2.1 Demo', 'description' => 'Template data kuantitatif demo.', 'row_definition' => ['periods' => ['2023', '2024', '2025']], 'validation_rules' => ['source_required' => true], 'is_required' => true, 'sort_order' => 1],
        );
        $columns = [
            ['key' => 'jumlah_dosen_tetap', 'label' => 'Jumlah dosen tetap homebase', 'type' => 'integer', 'unit' => 'orang', 'min' => 0, 'max' => 1000],
            ['key' => 'jumlah_mahasiswa_aktif', 'label' => 'Jumlah mahasiswa aktif', 'type' => 'integer', 'unit' => 'orang', 'min' => 0, 'max' => 100000],
            ['key' => 'lulusan_tepat_waktu', 'label' => 'Persentase lulusan tepat waktu', 'type' => 'decimal', 'unit' => 'persen', 'min' => 0, 'max' => 100],
            ['key' => 'publikasi_dosen', 'label' => 'Rasio publikasi terhadap dosen', 'type' => 'decimal', 'unit' => 'rasio', 'min' => 0, 'max' => 20],
        ];
        foreach ($columns as $index => $column) {
            LkpsTemplateColumn::query()->updateOrCreate(
                ['lkps_template_id' => $lkps->getKey(), 'column_key' => $column['key']],
                ['label' => $column['label'], 'data_type' => $column['type'], 'unit' => $column['unit'], 'is_required' => true, 'min_value' => $column['min'], 'max_value' => $column['max'], 'decimal_scale' => $column['type'] === 'decimal' ? 2 : null, 'source_type' => 'pangkalan_data_demo', 'sort_order' => $index + 1],
            );
        }
    }

    /** @param array<string, AccreditationCriterion> $criteria @param array<string, array{node:InstrumentNode, element:AssessmentElement}> $elements */
    private function seedAccreditation(PerguruanTinggi $college, ProgramStudi $program, InstrumentVersion $version, array $criteria, array $elements, User $actor): void
    {
        $suffix = $program->kode_prodi === 'S1-INFORMATIKA-DEMO' ? 'IF' : 'SI';
        $accreditation = Accreditation::query()->updateOrCreate(
            ['code' => 'AKR-LAM-INFOKOM-' . $suffix . '-2026'],
            [
                'perguruan_tinggi_id' => $college->getKey(),
                'program_studi_id' => $program->getKey(),
                'instrument_version_id' => $version->getKey(),
                'scope_type' => 'program_study',
                'title' => 'Akreditasi LAM INFOKOM 2.1 - ' . $program->nama_prodi,
                'status' => 'in_progress',
                'planned_submission_date' => '2026-11-30',
                'owner_id' => $actor->getKey(),
            ],
        );

        $evidenceCollection = EvidenceCollection::query()->updateOrCreate(
            ['code' => 'COLL-AKR-' . $suffix . '-2026'],
            [
                'perguruan_tinggi_id' => $college->getKey(), 'program_studi_id' => $program->getKey(), 'accreditation_id' => $accreditation->getKey(), 'created_by' => $actor->getKey(),
                'name' => 'Evidence Akreditasi ' . $program->nama_prodi, 'provider' => 'google_drive', 'root_folder_url' => 'https://drive.google.com/drive/folders/sqm-demo-' . strtolower($suffix), 'root_folder_id' => 'sqm-demo-' . strtolower($suffix), 'status' => 'in_progress', 'description' => 'Folder demo; seluruh tautan bersifat simulasi dan bukan dokumen resmi.',
            ],
        );

        $evidence = Evidence::query()->updateOrCreate(
            ['code' => 'EVD-AKR-' . $suffix . '-SPMI'],
            ['perguruan_tinggi_id' => $college->getKey(), 'program_studi_id' => $program->getKey(), 'created_by' => $actor->getKey(), 'title' => 'Laporan SPMI dan AMI ' . $program->nama_prodi . ' Tahun 2025', 'description' => 'Evidence sintetis agregat untuk simulasi akreditasi.', 'valid_from' => '2025-01-01', 'valid_until' => '2027-12-31', 'status' => 'verified', 'verified_by' => $actor->getKey(), 'verified_at' => now()],
        );
        EvidenceCollectionItem::query()->updateOrCreate(
            ['evidence_collection_id' => $evidenceCollection->getKey(), 'requirement_code' => 'KR-BM-EVIDENCE'],
            ['evidence_id' => $evidence->getKey(), 'requirement_title' => 'Laporan SPMI, AMI, dan tindak lanjut', 'target_type' => 'accreditation', 'target_id' => $accreditation->getKey(), 'is_required' => true, 'status' => 'verified', 'notes' => 'Data demo sintetis; ganti dengan tautan institusi saat produksi.'],
        );

        foreach (self::CRITERIA as $index => $criterion) {
            $section = AccreditationSection::query()->updateOrCreate(
                ['accreditation_id' => $accreditation->getKey(), 'code' => 'LED-' . $criterion['code']],
                ['instrument_node_id' => null, 'title' => $criterion['name'], 'section_type' => 'led', 'sort_order' => $index + 1, 'status' => $index < 2 ? 'submitted' : 'draft', 'readiness_percent' => $index < 2 ? 82 : 64],
            );
            $response = AccreditationResponse::query()->updateOrCreate(
                ['accreditation_id' => $accreditation->getKey(), 'response_key' => 'LED-' . $criterion['code']],
                ['accreditation_section_id' => $section->getKey(), 'instrument_node_id' => null, 'response_type' => 'text', 'response_text' => 'Program studi menjalankan siklus PPEPP melalui penetapan standar, pelaksanaan, evaluasi AMI, pengendalian melalui RTM, dan program peningkatan. Nilai pada record ini merupakan data sintetis demo yang harus diganti dengan narasi dan evidence resmi.', 'status' => $index < 2 ? 'submitted' : 'draft', 'last_edited_by' => $actor->getKey(), 'submitted_at' => $index < 2 ? now() : null],
            );
            EvidenceLink::query()->updateOrCreate(
                ['evidence_id' => $evidence->getKey(), 'linkable_type' => AccreditationResponse::class, 'linkable_id' => $response->getKey(), 'relation_type' => 'supporting'],
                ['citation_page' => 1, 'citation_note' => 'Tautan demo evidence SPMI/AMI untuk mendukung narasi kriteria.', 'is_required' => true],
            );
            AccreditationAssessment::query()->updateOrCreate(
                ['accreditation_id' => $accreditation->getKey(), 'accreditation_response_id' => $response->getKey(), 'assessment_type' => 'internal'],
                ['assessor_id' => $actor->getKey(), 'result' => $index < 2 ? 'met' : 'partially_met', 'score' => $index < 2 ? 3.7 : 3.1, 'notes' => 'Penilaian internal demo; perlu review asesor internal.', 'status' => 'completed', 'assessed_at' => now()],
            );
            AccreditationReadinessItem::query()->updateOrCreate(
                ['accreditation_id' => $accreditation->getKey(), 'item_type' => 'criterion', 'item_key' => $criterion['code']],
                ['status' => $index < 2 ? 'ready' : 'in_progress', 'notes' => $index < 2 ? 'Evidence dan narasi demo tersedia.' : 'Masih memerlukan penguatan evidence.', 'checked_at' => now(), 'checked_by' => $actor->getKey()],
            );
        }

        $this->seedMappings($version, $program, $criteria, $elements, $actor);
        $this->seedReadiness($accreditation, $version, $program, $elements, $actor);
        $this->seedSubmission($accreditation, $actor, $suffix);
    }

    /** @param array<string, AccreditationCriterion> $criteria @param array<string, array{node:InstrumentNode, element:AssessmentElement}> $elements */
    private function seedMappings(InstrumentVersion $version, ProgramStudi $program, array $criteria, array $elements, User $actor): void
    {
        foreach (self::ELEMENTS as $definition) {
            $spmiIndicator = SpmiIndicator::query()->where('perguruan_tinggi_id', $program->perguruan_tinggi_id)->where('code', $definition['indicator'])->first();
            if ($spmiIndicator === null) {
                continue;
            }
            $assessmentIndicator = AssessmentIndicator::query()
                ->where('assessment_element_id', $elements[$definition['code']]['element']->getKey())
                ->where('code', 'AI-' . $definition['code'])
                ->firstOrFail();

            InstrumentMapping::query()->updateOrCreate(
                ['instrument_version_id' => $version->getKey(), 'instrument_node_id' => $elements[$definition['code']]['node']->getKey(), 'accreditation_criterion_id' => $criteria['accreditation'][$definition['criterion']]->getKey(), 'target_type' => 'led'],
                ['source_indicator_id' => $assessmentIndicator->getKey(), 'target_element_id' => $elements[$definition['code']]['element']->getKey(), 'mapping_type' => 'primary', 'source_type' => 'spmi_indicator', 'target_key' => 'LED-' . $definition['criterion'], 'coverage_weight' => $definition['weight'], 'is_required' => true, 'approval_status' => 'approved', 'approved_by' => $actor->getKey(), 'approved_at' => now(), 'source_reference' => 'Mapping demo SPMI ke LAM INFOKOM', 'notes' => 'Mapping sintetis untuk demonstrasi lintas domain.'],
            );
        }
    }

    /** @param array<string, array{node:InstrumentNode, element:AssessmentElement}> $elements */
    private function seedReadiness(Accreditation $accreditation, InstrumentVersion $version, ProgramStudi $program, array $elements, User $actor): void
    {
        $run = ReadinessRun::query()->updateOrCreate(
            ['accreditation_id' => $accreditation->getKey(), 'run_type' => 'full'],
            ['instrument_version_id' => $version->getKey(), 'created_by' => $actor->getKey(), 'status' => 'completed', 'engine_version' => 'readiness-v1-demo', 'total_items' => count(self::ELEMENTS), 'ready_items' => 10, 'completion_percent' => 78.25, 'weighted_score' => 3.42, 'input_hash' => hash('sha256', 'akreditasi-demo-' . $program->getKey()), 'summary' => ['data_provenance' => 'synthetic_demo', 'catatan' => 'Hasil simulasi; bukan hasil asesmen resmi.'], 'started_at' => now()->subMinutes(20), 'completed_at' => now()],
        );
        foreach (self::ELEMENTS as $index => $definition) {
            $score = $index < 10 ? 3.6 : 2.8;
            $status = $index < 10 ? 'ready' : 'partial';
            $result = ReadinessResult::query()->updateOrCreate(
                ['readiness_run_id' => $run->getKey(), 'item_key' => $definition['code']],
                ['instrument_node_id' => $elements[$definition['code']]['node']->getKey(), 'assessment_element_id' => $elements[$definition['code']]['element']->getKey(), 'status' => $status, 'weight' => $definition['weight'], 'completion_percent' => $index < 10 ? 90 : 62, 'evidence_percent' => $index < 10 ? 88 : 50, 'score' => $score, 'gap_count' => $index < 10 ? 0 : 1, 'details' => ['rubric_label' => $index < 10 ? 'Baik' : 'Cukup', 'data_provenance' => 'synthetic_demo']],
            );
            if ($index >= 10) {
                ReadinessGap::query()->updateOrCreate(
                    ['readiness_run_id' => $run->getKey(), 'item_key' => $definition['code']],
                    ['readiness_result_id' => $result->getKey(), 'gap_type' => 'evidence', 'severity' => 'medium', 'description' => 'Evidence kuantitatif dan analisis elemen belum lengkap.', 'resolution_status' => 'open'],
                );
            }
        }
        $snapshotInput = ['run_id' => $run->getKey(), 'score' => 3.42, 'data_provenance' => 'synthetic_demo'];
        AccreditationScoreSnapshot::query()->firstOrCreate(
            ['snapshot_hash' => hash('sha256', json_encode($snapshotInput, JSON_THROW_ON_ERROR))],
            ['accreditation_id' => $accreditation->getKey(), 'instrument_version_id' => $version->getKey(), 'calculated_by' => $actor->getKey(), 'score' => 3.42, 'status' => 'calculated', 'rule_results' => ['status_qualification' => 'belum_memenuhi_unggul', 'score_total' => 3.42], 'input_snapshot' => $snapshotInput, 'calculated_at' => now()],
        );
    }

    private function seedSubmission(Accreditation $accreditation, User $actor, string $suffix): void
    {
        AccreditationSubmission::query()->updateOrCreate(
            ['accreditation_id' => $accreditation->getKey(), 'submission_no' => 1],
            ['package_hash' => hash('sha256', 'submission-demo-' . $accreditation->getKey()), 'status' => 'draft', 'submitted_by' => $actor->getKey(), 'notes' => 'Paket submission demo belum merupakan pengajuan resmi ke LAM INFOKOM.'],
        );
    }
}
