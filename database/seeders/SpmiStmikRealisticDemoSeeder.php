<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\SpmiEvaluation;
use App\Models\SpmiFramework;
use App\Models\SpmiImprovementProgram;
use App\Models\SpmiIndicator;
use App\Models\SpmiRealization;
use App\Models\SpmiStandard;
use App\Models\SpmiTarget;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Dataset SPMI realistis untuk demo sekolah tinggi informatika.
 *
 * Seluruh nilai operasional di seeder ini adalah data sintetis agregat untuk
 * demo. Struktur standar menggunakan pola umum SPMI dan bukan data resmi
 * institusi tertentu.
 */
final class SpmiStmikRealisticDemoSeeder extends Seeder
{
    private const FOUNDATION_CODE = 'YYS-STMIK-DEMO';
    private const COLLEGE_CODE = 'STMIK-NUSANTARA-DEMO';
    private const FRAMEWORK_CODE = 'SPMI-STMIK-2026';

    /** @var array<int, array<string, mixed>> */
    private const STANDARDS = [
        [
            'code' => 'STD-01',
            'name' => 'Standar Kompetensi Lulusan',
            'statement' => 'Lulusan memiliki kompetensi sesuai profil lulusan, capaian pembelajaran, dan kebutuhan pengguna.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-01', 'name' => 'Persentase lulusan yang lulus tepat waktu', 'definition' => 'Persentase lulusan yang menyelesaikan studi sesuai masa studi yang ditetapkan.', 'unit' => 'persen', 'target' => 70, 'source_type' => 'tracer_study'],
                ['code' => 'IND-02', 'name' => 'Persentase lulusan dengan IPK minimal 3,00', 'definition' => 'Proporsi lulusan dengan IPK akhir minimal 3,00.', 'unit' => 'persen', 'target' => 75, 'source_type' => 'sistem_akademik'],
            ],
        ],
        [
            'code' => 'STD-02',
            'name' => 'Standar Isi Pembelajaran',
            'statement' => 'Isi pembelajaran sesuai kurikulum, capaian pembelajaran lulusan, perkembangan keilmuan, dan kebutuhan dunia kerja.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-03', 'name' => 'Persentase mata kuliah memiliki RPS tervalidasi', 'definition' => 'Proporsi mata kuliah yang memiliki RPS dan telah divalidasi oleh program studi.', 'unit' => 'persen', 'target' => 100, 'source_type' => 'audit_dokumen'],
                ['code' => 'IND-04', 'name' => 'Persentase RPS yang diperbarui dalam dua tahun terakhir', 'definition' => 'Proporsi RPS yang ditinjau dan diperbarui sesuai perkembangan keilmuan.', 'unit' => 'persen', 'target' => 85, 'source_type' => 'audit_dokumen'],
            ],
        ],
        [
            'code' => 'STD-03',
            'name' => 'Standar Proses Pembelajaran',
            'statement' => 'Proses pembelajaran direncanakan, dilaksanakan, dipantau, dan ditingkatkan secara berkelanjutan.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-05', 'name' => 'Persentase perkuliahan terlaksana sesuai kalender akademik', 'definition' => 'Proporsi perkuliahan yang terlaksana sesuai jadwal dan kalender akademik.', 'unit' => 'persen', 'target' => 95, 'source_type' => 'sistem_akademik'],
                ['code' => 'IND-06', 'name' => 'Persentase mata kuliah menggunakan LMS', 'definition' => 'Proporsi mata kuliah yang memanfaatkan LMS secara aktif.', 'unit' => 'persen', 'target' => 80, 'source_type' => 'log_lms'],
            ],
        ],
        [
            'code' => 'STD-04',
            'name' => 'Standar Penilaian Pembelajaran',
            'statement' => 'Penilaian pembelajaran dilaksanakan objektif, transparan, edukatif, dan sesuai capaian pembelajaran.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-07', 'name' => 'Persentase mata kuliah memiliki rubrik penilaian', 'definition' => 'Proporsi mata kuliah yang menggunakan rubrik penilaian terdokumentasi.', 'unit' => 'persen', 'target' => 90, 'source_type' => 'audit_dokumen'],
            ],
        ],
        [
            'code' => 'STD-05',
            'name' => 'Standar Dosen dan Tenaga Kependidikan',
            'statement' => 'Dosen dan tenaga kependidikan memiliki kualifikasi, kompetensi, dan pengembangan profesional yang sesuai.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-08', 'name' => 'Persentase dosen sesuai bidang keilmuan', 'definition' => 'Proporsi dosen yang memiliki kualifikasi dan keahlian sesuai bidang program studi.', 'unit' => 'persen', 'target' => 90, 'source_type' => 'pangkalan_data_sdm'],
                ['code' => 'IND-09', 'name' => 'Persentase dosen mengikuti pengembangan kompetensi', 'definition' => 'Proporsi dosen yang mengikuti pelatihan, sertifikasi, atau kegiatan pengembangan kompetensi.', 'unit' => 'persen', 'target' => 70, 'source_type' => 'laporan_sdm'],
            ],
        ],
        [
            'code' => 'STD-06',
            'name' => 'Standar Sarana dan Prasarana',
            'statement' => 'Sarana dan prasarana pembelajaran tersedia, layak, mudah diakses, dan dipelihara.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-10', 'name' => 'Persentase ruang pembelajaran memenuhi standar kelayakan', 'definition' => 'Proporsi ruang pembelajaran yang memenuhi standar kapasitas, keselamatan, dan fasilitas.', 'unit' => 'persen', 'target' => 90, 'source_type' => 'audit_sarpras'],
                ['code' => 'IND-11', 'name' => 'Ketersediaan laboratorium praktik', 'definition' => 'Persentase kebutuhan praktik yang dapat dilayani oleh laboratorium aktif.', 'unit' => 'persen', 'target' => 85, 'source_type' => 'inventaris_sarpras'],
            ],
        ],
        [
            'code' => 'STD-07',
            'name' => 'Standar Pengelolaan Pembelajaran',
            'statement' => 'Pengelolaan pembelajaran dilakukan melalui perencanaan, pelaksanaan, monitoring, evaluasi, dan tindak lanjut.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-12', 'name' => 'Persentase program studi melakukan evaluasi pembelajaran semesteran', 'definition' => 'Proporsi program studi yang menyusun dan menindaklanjuti evaluasi pembelajaran setiap semester.', 'unit' => 'persen', 'target' => 100, 'source_type' => 'laporan_evaluasi'],
            ],
        ],
        [
            'code' => 'STD-08',
            'name' => 'Standar Pembiayaan Pembelajaran',
            'statement' => 'Pembiayaan pembelajaran direncanakan, dialokasikan, digunakan, dan dievaluasi secara akuntabel.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-13', 'name' => 'Persentase program kerja pembelajaran terealisasi', 'definition' => 'Proporsi program kerja pembelajaran yang memperoleh pendanaan dan terlaksana.', 'unit' => 'persen', 'target' => 85, 'source_type' => 'laporan_keuangan'],
            ],
        ],
        [
            'code' => 'STD-09',
            'name' => 'Standar Penelitian',
            'statement' => 'Penelitian dilaksanakan sesuai roadmap, etika, kompetensi, dan menghasilkan luaran yang relevan.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-14', 'name' => 'Rasio judul penelitian terhadap dosen', 'definition' => 'Perbandingan jumlah judul penelitian dengan jumlah dosen tetap.', 'unit' => 'rasio', 'target' => 0.60, 'source_type' => 'laporan_penelitian'],
                ['code' => 'IND-15', 'name' => 'Persentase penelitian memiliki luaran terdokumentasi', 'definition' => 'Proporsi penelitian yang memiliki publikasi, HKI, produk, atau luaran lain yang terdokumentasi.', 'unit' => 'persen', 'target' => 70, 'source_type' => 'laporan_penelitian'],
            ],
        ],
        [
            'code' => 'STD-10',
            'name' => 'Standar Pengabdian kepada Masyarakat',
            'statement' => 'Pengabdian kepada masyarakat dilaksanakan berbasis kebutuhan mitra dan kepakaran sivitas akademika.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-16', 'name' => 'Rasio kegiatan pengabdian terhadap dosen', 'definition' => 'Perbandingan jumlah kegiatan pengabdian kepada masyarakat dengan jumlah dosen tetap.', 'unit' => 'rasio', 'target' => 0.40, 'source_type' => 'laporan_pkm'],
                ['code' => 'IND-17', 'name' => 'Persentase kegiatan pengabdian memiliki evaluasi dampak', 'definition' => 'Proporsi kegiatan pengabdian yang memiliki evaluasi manfaat atau dampak bagi mitra.', 'unit' => 'persen', 'target' => 65, 'source_type' => 'laporan_pkm'],
            ],
        ],
        [
            'code' => 'STD-11',
            'name' => 'Standar Tata Kelola dan Kerja Sama',
            'statement' => 'Tata kelola dilaksanakan dengan prinsip kredibel, transparan, akuntabel, bertanggung jawab, dan adil.',
            'basis' => 'SPMI dan tata kelola perguruan tinggi',
            'indicators' => [
                ['code' => 'IND-18', 'name' => 'Persentase keputusan unit terdokumentasi', 'definition' => 'Proporsi keputusan penting unit yang memiliki notulen atau dokumen keputusan.', 'unit' => 'persen', 'target' => 95, 'source_type' => 'audit_tata_kelola'],
            ],
        ],
        [
            'code' => 'STD-12',
            'name' => 'Standar Sistem Penjaminan Mutu Internal',
            'statement' => 'SPMI dilaksanakan secara sistemik dan berkelanjutan melalui siklus PPEPP.',
            'basis' => 'SPMI dan Permendikti Saintek',
            'indicators' => [
                ['code' => 'IND-19', 'name' => 'Persentase standar memiliki bukti pelaksanaan', 'definition' => 'Proporsi standar mutu yang memiliki evidence pelaksanaan yang dapat diverifikasi.', 'unit' => 'persen', 'target' => 95, 'source_type' => 'audit_mutu'],
                ['code' => 'IND-20', 'name' => 'Persentase hasil AMI ditindaklanjuti', 'definition' => 'Proporsi temuan AMI yang memiliki tindak lanjut dengan status terpantau.', 'unit' => 'persen', 'target' => 100, 'source_type' => 'laporan_ami'],
            ],
        ],
        [
            'code' => 'STD-13',
            'name' => 'Standar Kemahasiswaan',
            'statement' => 'Layanan kemahasiswaan mendukung pengembangan akademik, karakter, karier, dan kesejahteraan mahasiswa.',
            'basis' => 'SPMI dan SN-Dikti',
            'indicators' => [
                ['code' => 'IND-21', 'name' => 'Tingkat kepuasan mahasiswa terhadap layanan', 'definition' => 'Persentase mahasiswa yang menyatakan puas terhadap layanan akademik dan kemahasiswaan.', 'unit' => 'persen', 'target' => 85, 'source_type' => 'survei_mahasiswa'],
            ],
        ],
        [
            'code' => 'STD-14',
            'name' => 'Standar Tracer Study dan Relevansi Lulusan',
            'statement' => 'Perguruan tinggi memantau masa transisi, relevansi pekerjaan, dan umpan balik pengguna lulusan.',
            'basis' => 'SPMI dan kebutuhan akreditasi',
            'indicators' => [
                ['code' => 'IND-22', 'name' => 'Tingkat respons tracer study', 'definition' => 'Persentase lulusan yang memberikan respons pada tracer study.', 'unit' => 'persen', 'target' => 60, 'source_type' => 'tracer_study'],
                ['code' => 'IND-23', 'name' => 'Persentase lulusan bekerja sesuai bidang', 'definition' => 'Proporsi lulusan yang bekerja atau berwirausaha sesuai bidang kompetensi.', 'unit' => 'persen', 'target' => 70, 'source_type' => 'tracer_study'],
            ],
        ],
    ];

    /** @var array<string, array<int, float>> */
    private const REALIZATION_FACTORS = [
        'S1-INFORMATIKA-DEMO' => [0.91, 0.88, 0.97, 0.93, 0.96, 0.89, 0.94, 0.95, 0.84, 0.90, 0.92, 0.86, 0.81, 0.78, 0.83, 0.88, 0.76, 0.93, 0.74, 0.69, 0.87, 0.63, 0.71],
        'S1-SISTEM-INFORMASI-DEMO' => [0.86, 0.91, 0.95, 0.88, 0.93, 0.84, 0.90, 0.87, 0.79, 0.86, 0.89, 0.83, 0.77, 0.72, 0.80, 0.82, 0.70, 0.91, 0.67, 0.61, 0.82, 0.58, 0.66],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $foundation = Yayasan::query()->firstOrCreate(
                ['kode' => self::FOUNDATION_CODE],
                ['nama' => 'Yayasan Pendidikan Teknologi Nusantara'],
            );

            $college = PerguruanTinggi::query()->firstOrCreate(
                ['kode_pt' => self::COLLEGE_CODE],
                [
                    'yayasan_id' => $foundation->getKey(),
                    'nama_pt' => 'Sekolah Tinggi Manajemen Informatika Nusantara',
                    'jenis' => 'sekolah_tinggi',
                    'status' => 'active',
                ],
            );

            $programs = [
                ['code' => 'S1-INFORMATIKA-DEMO', 'name' => 'S1 Informatika', 'level' => 'S1'],
                ['code' => 'S1-SISTEM-INFORMASI-DEMO', 'name' => 'S1 Sistem Informasi', 'level' => 'S1'],
            ];

            $actor = User::query()->where('perguruan_tinggi_id', $college->getKey())->first()
                ?? User::query()->first();

            $framework = SpmiFramework::query()->firstOrCreate(
                ['perguruan_tinggi_id' => $college->getKey(), 'code' => self::FRAMEWORK_CODE],
                [
                    'name' => 'Framework SPMI STMIK Nusantara 2026',
                    'version_label' => '2026.1',
                    'status' => 'active',
                    'effective_from' => '2026-01-01',
                    'description' => 'Dataset demo realistis untuk simulasi siklus PPEPP pada sekolah tinggi informatika.',
                ],
            );

            foreach ($programs as $program) {
                $study = ProgramStudi::query()->firstOrCreate(
                    ['kode_prodi' => $program['code']],
                    [
                        'perguruan_tinggi_id' => $college->getKey(),
                        'nama_prodi' => $program['name'],
                        'jenjang' => $program['level'],
                        'status' => 'active',
                    ],
                );

                $this->seedProgram($college, $study, $framework, $actor, $program['code']);
            }
        });
    }

    private function seedProgram(PerguruanTinggi $college, ProgramStudi $study, SpmiFramework $framework, ?User $actor, string $programCode): void
    {
        $factors = self::REALIZATION_FACTORS[$programCode] ?? [];
        $indicatorPosition = 0;

        foreach (self::STANDARDS as $standardDefinition) {
            $standard = SpmiStandard::query()->firstOrCreate(
                [
                    'spmi_framework_id' => $framework->getKey(),
                    'code' => $standardDefinition['code'],
                ],
                [
                    'perguruan_tinggi_id' => $college->getKey(),
                    'program_studi_id' => $study->getKey(),
                    'name' => $standardDefinition['name'],
                    'statement' => $standardDefinition['statement'],
                    'basis' => $standardDefinition['basis'],
                    'status' => 'active',
                    'sort_order' => (int) substr($standardDefinition['code'], -2),
                ],
            );

            foreach ($standardDefinition['indicators'] as $indicatorDefinition) {
                $indicator = SpmiIndicator::query()->firstOrCreate(
                    [
                        'spmi_standard_id' => $standard->getKey(),
                        'code' => $indicatorDefinition['code'],
                    ],
                    [
                        'perguruan_tinggi_id' => $college->getKey(),
                        'name' => $indicatorDefinition['name'],
                        'definition' => $indicatorDefinition['definition'],
                        'measurement_type' => in_array($indicatorDefinition['unit'], ['persen', 'rasio'], true) ? 'numeric' : 'numeric',
                        'unit' => $indicatorDefinition['unit'],
                        'weight' => 100,
                        'status' => 'active',
                        'validation_rules' => [
                            'source_type' => $indicatorDefinition['source_type'],
                            'data_provenance' => 'synthetic_demo',
                            'source_note' => 'Nilai agregat sintetis untuk demonstrasi SQM, bukan data resmi institusi.',
                        ],
                    ],
                );

                foreach ([2025, 2026] as $year) {
                    $targetValue = (float) $indicatorDefinition['target'];
                    $target = SpmiTarget::query()->firstOrCreate(
                        [
                            'spmi_indicator_id' => $indicator->getKey(),
                            'program_studi_id' => $study->getKey(),
                            'period_year' => $year,
                            'period_code' => "TA-{$year}",
                        ],
                        [
                            'perguruan_tinggi_id' => $college->getKey(),
                            'target_numeric' => $targetValue,
                            'status' => 'approved',
                            'set_by' => $actor?->getKey(),
                        ],
                    );

                    $factor = (float) ($factors[$indicatorPosition] ?? 0.85);
                    $yearAdjustment = $year === 2026 ? 1.0 : 0.96;
                    $realizationValue = round($targetValue * $factor * $yearAdjustment, 4);
                    $achievement = $targetValue > 0 ? round(($realizationValue / $targetValue) * 100, 4) : 0.0;
                    $result = $this->resultFor($achievement);

                    $realization = SpmiRealization::query()->firstOrCreate(
                        [
                            'spmi_target_id' => $target->getKey(),
                            'spmi_indicator_id' => $indicator->getKey(),
                            'period_year' => $year,
                        ],
                        [
                            'perguruan_tinggi_id' => $college->getKey(),
                            'program_studi_id' => $study->getKey(),
                            'realization_numeric' => $realizationValue,
                            'source_type' => $indicatorDefinition['source_type'],
                            'source_reference' => "Dataset demo sintetis {$year}; indikator {$indicatorDefinition['code']}.",
                            'status' => $year === 2025 ? 'verified' : 'submitted',
                            'recorded_by' => $actor?->getKey(),
                            'verified_by' => $year === 2025 ? $actor?->getKey() : null,
                            'verified_at' => $year === 2025 ? now() : null,
                            'verification_notes' => $year === 2025 ? 'Realisasi demo periode sebelumnya telah diverifikasi untuk simulasi.' : null,
                        ],
                    );

                    $evaluation = SpmiEvaluation::query()->firstOrCreate(
                        ['spmi_realization_id' => $realization->getKey()],
                        [
                            'perguruan_tinggi_id' => $college->getKey(),
                            'program_studi_id' => $study->getKey(),
                            'result' => $result,
                            'achievement_percentage' => $achievement,
                            'analysis' => $this->analysisFor($result, $indicatorDefinition['name'], $year),
                            'root_cause' => $result === 'met' ? null : 'Dokumentasi, konsistensi pelaksanaan, dan monitoring belum merata pada seluruh unit.',
                            'recommendation' => $result === 'met' ? 'Pertahankan praktik baik dan lakukan monitoring berkala.' : 'Susun tindakan perbaikan, tetapkan owner, dan lakukan verifikasi ulang.',
                            'status' => 'completed',
                            'evaluated_by' => $actor?->getKey(),
                            'evaluated_at' => now(),
                        ],
                    );

                    if ($result !== 'met') {
                        SpmiImprovementProgram::query()->firstOrCreate(
                            [
                                'perguruan_tinggi_id' => $college->getKey(),
                                'code' => "IMPROVE-{$programCode}-{$indicatorDefinition['code']}-{$year}",
                            ],
                            [
                                'spmi_evaluation_id' => $evaluation->getKey(),
                                'spmi_indicator_id' => $indicator->getKey(),
                                'spmi_target_id' => $target->getKey(),
                                'program_studi_id' => $study->getKey(),
                                'title' => "Program peningkatan {$indicatorDefinition['name']}",
                                'action_plan' => 'Analisis akar masalah, tetapkan PIC, perbarui bukti pelaksanaan, lakukan monitoring bulanan, dan verifikasi hasil pada siklus PPEPP berikutnya.',
                                'owner_id' => $actor?->getKey(),
                                'due_date' => $year === 2025 ? "{$year}-12-15" : '2026-12-15',
                                'progress_percent' => $year === 2025 ? 100 : 35,
                                'status' => $year === 2025 ? 'completed' : 'in_progress',
                                'completion_notes' => $year === 2025 ? 'Program demo periode sebelumnya selesai dan siap menjadi input evaluasi berikutnya.' : 'Program demo sedang berjalan untuk memperbaiki capaian indikator.',
                                'verified_by' => $year === 2025 ? $actor?->getKey() : null,
                                'verified_at' => $year === 2025 ? now() : null,
                            ],
                        );
                    }
                }

                $indicatorPosition++;
            }
        }
    }

    private function resultFor(float $achievement): string
    {
        return match (true) {
            $achievement >= 100 => 'met',
            $achievement >= 80 => 'partially_met',
            default => 'not_met',
        };
    }

    private function analysisFor(string $result, string $indicatorName, int $year): string
    {
        return match ($result) {
            'met' => "Indikator {$indicatorName} pada periode {$year} telah mencapai atau melampaui target demo.",
            'partially_met' => "Indikator {$indicatorName} pada periode {$year} mendekati target tetapi masih membutuhkan pengendalian dan peningkatan.",
            default => "Indikator {$indicatorName} pada periode {$year} belum mencapai target demo dan memerlukan program peningkatan prioritas.",
        };
    }
}
