<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AmiAssignment;
use App\Models\AmiChecklistItem;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\RtlAction;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\RtmParticipant;
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
use Illuminate\Support\Facades\Hash;

/**
 * Seeder Demo SPMI Lengkap – STMIK Nusantara Informatika.
 *
 * Mensimulasikan satu siklus penuh PPEPP (Penetapan, Pelaksanaan, Evaluasi,
 * Pengendalian, Peningkatan) untuk keperluan demonstrasi sistem QMS:
 *
 * ┌───────────────────────────────────────────────────────────────┐
 * │ Yayasan Maju Bersama Nusantara                                 │
 * │   └─ STMIK Nusantara Informatika                              │
 * │        ├─ Prodi: Teknik Informatika (S1)                      │
 * │        └─ Prodi: Sistem Informasi (S1)                        │
 * └───────────────────────────────────────────────────────────────┘
 *
 * Data yang diisi:
 *  - Fondasi Organisasi (Yayasan, PT, Prodi, Users Demo)
 *  - SPMI Framework & 9 Standar Mutu berbasis SN-Dikti
 *  - ~35 Indikator Mutu dengan Target & Realisasi TA 2023/2024
 *  - Evaluasi Ketercapaian (SPMI Evaluations)
 *  - AMI Internal Semester Genap 2023/2024 (Checklist + 5 Temuan)
 *  - RTM Semester Genap 2023/2024 (6 Keputusan)
 *  - RTL (5 Rencana Tindak Lanjut dengan status bervariasi)
 *
 * Sumber data referensi kondisi riil STMIK swasta menengah Indonesia:
 *  - Standar SN-Dikti (Permendikbudristek No. 53/2023 & Permendiktisaintek No. 39/2025)
 *  - Data umum statistik BAN-PT/LLDIKTI untuk STMIK/Institut Komputer
 *  - Praktik umum SPMI yang dilakukan LPMI di perguruan tinggi sejenis
 */
final class StmikNusantaraDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $yayasan = $this->seedOrganisasi();

            $pt = PerguruanTinggi::query()
                ->where('yayasan_id', $yayasan->getKey())
                ->where('kode_pt', 'STMIK-NI-001')
                ->firstOrFail();

            $prodiTi = ProgramStudi::query()
                ->where('perguruan_tinggi_id', $pt->getKey())
                ->where('kode_prodi', 'TI-001')
                ->firstOrFail();

            $prodiSi = ProgramStudi::query()
                ->where('perguruan_tinggi_id', $pt->getKey())
                ->where('kode_prodi', 'SI-001')
                ->firstOrFail();

            $users = $this->seedUsers($pt, $prodiTi, $prodiSi);

            $framework = $this->seedSpmiFramework($pt);
            $standards = $this->seedStandarMutu($framework, $pt);
            $indicators = $this->seedIndikator($standards, $pt);
            $this->seedTargetDanRealisasi($indicators, $pt, $prodiTi, $prodiSi, $users);

            $amiCycle = $this->seedAmiCycle($pt, $prodiTi, $prodiSi, $users);
            $this->seedAmiChecklist($amiCycle, $standards, $indicators);
            $findings = $this->seedAmiFindings($amiCycle, $users);

            $rtm = $this->seedRtm($pt, $amiCycle, $users, $findings);
            $this->seedRtlActions($pt, $prodiTi, $prodiSi, $findings, $rtm['decisions'], $users);
        });
    }

    private function seedOrganisasi(): Yayasan
    {
        $yayasan = Yayasan::query()->firstOrCreate(
            ['kode' => 'YMB-NUS-001'],
            ['nama' => 'Yayasan Maju Bersama Nusantara'],
        );

        $pt = PerguruanTinggi::query()->firstOrCreate(
            ['yayasan_id' => $yayasan->getKey(), 'kode_pt' => 'STMIK-NI-001'],
            [
                'nama_pt' => 'STMIK Nusantara Informatika',
                'jenis' => 'Sekolah Tinggi',
                'status' => 'active',
            ],
        );

        ProgramStudi::query()->firstOrCreate(
            ['perguruan_tinggi_id' => $pt->getKey(), 'kode_prodi' => 'TI-001'],
            [
                'nama_prodi' => 'Teknik Informatika',
                'jenjang' => 'S1',
                'status' => 'active',
            ],
        );

        ProgramStudi::query()->firstOrCreate(
            ['perguruan_tinggi_id' => $pt->getKey(), 'kode_prodi' => 'SI-001'],
            [
                'nama_prodi' => 'Sistem Informasi',
                'jenjang' => 'S1',
                'status' => 'active',
            ],
        );

        return $yayasan;
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(PerguruanTinggi $pt, ProgramStudi $prodiTi, ProgramStudi $prodiSi): array
    {
        $demoUsers = [
            'ketua_stmik' => [
                'name' => 'Dr. Ir. Agung Wibowo, M.Kom.',
                'email' => 'ketua@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'institution',
                'default_scope_id' => $pt->getKey(),
            ],
            'kepala_lpmi' => [
                'name' => 'Drs. Bambang Setiawan, M.M.',
                'email' => 'lpmi@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'institution',
                'default_scope_id' => $pt->getKey(),
            ],
            'auditor_1' => [
                'name' => 'Siti Rahayu, S.Kom., M.T.',
                'email' => 'auditor1@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'institution',
                'default_scope_id' => $pt->getKey(),
            ],
            'auditor_2' => [
                'name' => 'Hendra Kusuma, S.T., M.Kom.',
                'email' => 'auditor2@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'institution',
                'default_scope_id' => $pt->getKey(),
            ],
            'kaprodi_ti' => [
                'name' => 'Rizky Pratama, S.Kom., M.Cs.',
                'email' => 'kaprodi.ti@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'program_study',
                'default_scope_id' => $prodiTi->getKey(),
            ],
            'kaprodi_si' => [
                'name' => 'Dewi Anggraini, S.Si., M.Kom.',
                'email' => 'kaprodi.si@stmik-nusantara.demo',
                'password' => Hash::make('demo-password'),
                'yayasan_id' => $pt->yayasan_id,
                'perguruan_tinggi_id' => $pt->getKey(),
                'default_scope_type' => 'program_study',
                'default_scope_id' => $prodiSi->getKey(),
            ],
        ];

        $createdUsers = [];
        foreach ($demoUsers as $key => $data) {
            $createdUsers[$key] = User::query()->firstOrCreate(
                ['email' => $data['email']],
                $data,
            );
        }

        return $createdUsers;
    }

    private function seedSpmiFramework(PerguruanTinggi $pt): SpmiFramework
    {
        return SpmiFramework::query()->firstOrCreate(
            ['perguruan_tinggi_id' => $pt->getKey(), 'code' => 'SPF-STMIK-NI-2023'],
            [
                'name' => 'Sistem Penjaminan Mutu Internal STMIK Nusantara Informatika',
                'version_label' => '2.0',
                'status' => 'active',
                'effective_from' => '2023-09-01',
                'effective_until' => null,
                'description' => 'Framework SPMI STMIK Nusantara Informatika berbasis SN-Dikti (Permendikbudristek No. 53 Tahun 2023 & Permendiktisaintek No. 39/2025), menggunakan siklus PPEPP (Penetapan, Pelaksanaan, Evaluasi, Pengendalian, Peningkatan).',
            ],
        );
    }

    /**
     * @return array<string, SpmiStandard>
     */
    private function seedStandarMutu(SpmiFramework $framework, PerguruanTinggi $pt): array
    {
        $standards = [
            'skl' => [
                'code' => 'STD-01-SKL',
                'name' => 'Standar 1: Standar Kompetensi Lulusan (SKL)',
                'statement' => 'STMIK Nusantara Informatika menetapkan Capaian Pembelajaran Lulusan (CPL) yang relevan dengan kebutuhan industri teknologi informasi, mengacu KKNI Level 6 dan SN-Dikti, yang dirumuskan bersama pemangku kepentingan (DUDIKA) secara berkala setiap 4 tahun.',
                'basis' => 'SN-Dikti Pasal 5-6, Permendikbudristek No. 53/2023, KKNI Level 6',
                'sort_order' => 1,
            ],
            'isi' => [
                'code' => 'STD-02-ISI',
                'name' => 'Standar 2: Standar Isi Pembelajaran (Kurikulum OBE)',
                'statement' => 'Kurikulum setiap program studi disusun berbasis Outcome-Based Education (OBE), memuati mata kuliah inti bidang infokom, mendukung fleksibilitas MBKM, dan dikaji ulang setiap 4 tahun bersama pemangku kepentingan internal dan eksternal.',
                'basis' => 'SN-Dikti Pasal 7-8, Kerangka Kualifikasi Nasional Indonesia (KKNI)',
                'sort_order' => 2,
            ],
            'proses' => [
                'code' => 'STD-03-PROSES',
                'name' => 'Standar 3: Standar Proses Pembelajaran',
                'statement' => 'Proses pembelajaran di STMIK Nusantara Informatika dilaksanakan secara interaktif, holistik, integratif, saintifik, kontekstual, tematik, efektif, kolaboratif, dan berpusat pada mahasiswa (Student-Centered Learning), memanfaatkan teknologi digital (LMS) secara optimal.',
                'basis' => 'SN-Dikti Pasal 9-11, Panduan Merdeka Belajar Kampus Merdeka (MBKM)',
                'sort_order' => 3,
            ],
            'penilaian' => [
                'code' => 'STD-04-PENILAIAN',
                'name' => 'Standar 4: Standar Penilaian Pembelajaran',
                'statement' => 'Penilaian pembelajaran dilakukan secara autentik, edukatif, objektif, akuntabel, dan transparan, mencakup penilaian proses (formatif) dan penilaian hasil (sumatif) yang mengukur ketercapaian CPL mahasiswa secara terukur dan terdokumentasi.',
                'basis' => 'SN-Dikti Pasal 12-14',
                'sort_order' => 4,
            ],
            'dosen' => [
                'code' => 'STD-05-SDM',
                'name' => 'Standar 5: Standar Dosen dan Tenaga Kependidikan',
                'statement' => 'Dosen Tetap Program Studi (DTPS) memiliki kualifikasi akademik minimal Magister (S2), kesesuaian bidang ilmu, rasio dosen:mahasiswa tidak melebihi 1:35, persentase bergelar Doktor (S3) minimal 20%, dan menjalankan Tri Dharma PT secara penuh.',
                'basis' => 'SN-Dikti Pasal 15-17, Permenpan No. 17 Tahun 2013 tentang Jabatan Fungsional Dosen',
                'sort_order' => 5,
            ],
            'sarpras' => [
                'code' => 'STD-06-SARPRAS',
                'name' => 'Standar 6: Standar Sarana dan Prasarana Pembelajaran',
                'statement' => 'Sarana dan prasarana pembelajaran mencakup ruang kuliah dengan kapasitas memadai, laboratorium komputer dengan spesifikasi terkini (diperbarui maksimal 3 tahun sekali), perpustakaan digital/e-library, jaringan internet kampus ≥100 Mbps, dan akses disabilitas.',
                'basis' => 'SN-Dikti Pasal 18-19, Permen PUPR No. 14/2017 (aksesibilitas disabilitas)',
                'sort_order' => 6,
            ],
            'pengelolaan' => [
                'code' => 'STD-07-PENGELOLAAN',
                'name' => 'Standar 7: Standar Pengelolaan Pembelajaran',
                'statement' => 'Perguruan tinggi mengelola kegiatan pembelajaran secara terencana, terstruktur, dan terukur melalui rencana pembelajaran semester (RPS), kalender akademik, monitoring perkuliahan, dan evaluasi berkala atas keterlaksanaan proses pembelajaran.',
                'basis' => 'SN-Dikti Pasal 20-21',
                'sort_order' => 7,
            ],
            'pembiayaan' => [
                'code' => 'STD-08-PEMBIAYAAN',
                'name' => 'Standar 8: Standar Pembiayaan Pembelajaran',
                'statement' => 'Perguruan tinggi menjamin pembiayaan operasional pembelajaran per mahasiswa per semester minimal Rp 2.400.000,- untuk program sarjana, dengan alokasi dana penelitian dan PkM dosen minimal 10% dari total anggaran pendidikan.',
                'basis' => 'SN-Dikti Pasal 22-23, Permendikbudristek No. 2 Tahun 2022 tentang BKT',
                'sort_order' => 8,
            ],
            'penelitian_pkm' => [
                'code' => 'STD-09-RISTEKPKM',
                'name' => 'Standar 9: Standar Penelitian dan Pengabdian kepada Masyarakat',
                'statement' => 'Setiap Dosen Tetap Program Studi melaksanakan penelitian minimal 1 kali per tahun dan pengabdian kepada masyarakat minimal 1 kali per tahun, dengan target publikasi ilmiah jurnal nasional/internasional minimal 0,5 artikel per dosen per tahun.',
                'basis' => 'SN-Dikti Pasal 24-36, Permenristekdikti No. 20/2017 tentang Tunjangan Profesi dan Kehormatan',
                'sort_order' => 9,
            ],
        ];

        $result = [];
        foreach ($standards as $key => $data) {
            $result[$key] = SpmiStandard::query()->firstOrCreate(
                ['spmi_framework_id' => $framework->getKey(), 'code' => $data['code']],
                array_merge($data, ['perguruan_tinggi_id' => $pt->getKey(), 'status' => 'active']),
            );
        }

        return $result;
    }

    /**
     * @param array<string, SpmiStandard> $standards
     * @return array<string, SpmiIndicator>
     */
    private function seedIndikator(array $standards, PerguruanTinggi $pt): array
    {
        $indicatorDefs = [
            // Standar 1 – Kompetensi Lulusan
            'skl_cpl_review' => [
                'std_key' => 'skl', 'code' => 'IKU-1.1',
                'name' => 'Frekuensi Peninjauan CPL Bersama Pemangku Kepentingan',
                'definition' => 'Jumlah kali peninjauan Capaian Pembelajaran Lulusan (CPL) bersama DUDIKA dan pemangku kepentingan eksternal dalam 4 tahun terakhir.',
                'measurement_type' => 'numeric', 'unit' => 'kali/4 tahun',
            ],
            'skl_keselarasan_dudika' => [
                'std_key' => 'skl', 'code' => 'IKU-1.2',
                'name' => 'Persentase Kepuasan Pengguna Lulusan oleh DUDIKA',
                'definition' => 'Persentase DUDIKA yang menyatakan puas atau sangat puas terhadap kompetensi dan etos kerja lulusan berdasarkan survei tracer study tahunan.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'skl_waktu_tunggu' => [
                'std_key' => 'skl', 'code' => 'IKT-1.3',
                'name' => 'Rata-Rata Masa Tunggu Kerja Lulusan',
                'definition' => 'Rata-rata waktu yang dibutuhkan lulusan untuk mendapatkan pekerjaan pertama setelah wisuda, diukur melalui tracer study.',
                'measurement_type' => 'numeric', 'unit' => 'bulan',
            ],
            'skl_ipk_lulusan' => [
                'std_key' => 'skl', 'code' => 'IKT-1.4',
                'name' => 'Rata-Rata IPK Lulusan',
                'definition' => 'Rata-rata Indeks Prestasi Kumulatif (IPK) semua lulusan program sarjana pada satu tahun akademik.',
                'measurement_type' => 'numeric', 'unit' => 'skala 4.0',
            ],

            // Standar 2 – Isi Pembelajaran / Kurikulum
            'isi_obe_coverage' => [
                'std_key' => 'isi', 'code' => 'IKU-2.1',
                'name' => 'Persentase Mata Kuliah dengan RPS berbasis OBE',
                'definition' => 'Persentase mata kuliah yang telah memiliki Rencana Pembelajaran Semester (RPS) berbasis OBE yang memuat CPL, Sub-CPMK, metode, dan penilaian autentik.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'isi_mbkm' => [
                'std_key' => 'isi', 'code' => 'IKT-2.2',
                'name' => 'Persentase Mahasiswa yang Mengikuti Program MBKM',
                'definition' => 'Persentase mahasiswa aktif semester 5 ke atas yang telah mengikuti minimal satu kegiatan Merdeka Belajar Kampus Merdeka (magang, pertukaran pelajar, atau proyek mandiri).',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],

            // Standar 3 – Proses Pembelajaran
            'proses_kehadiran_dosen' => [
                'std_key' => 'proses', 'code' => 'IKU-3.1',
                'name' => 'Persentase Kehadiran Dosen dalam Perkuliahan',
                'definition' => 'Persentase kelas yang terlaksana sesuai Rencana Pembelajaran Semester (minimal 14 pertemuan per semester per mata kuliah).',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'proses_lms' => [
                'std_key' => 'proses', 'code' => 'IKT-3.2',
                'name' => 'Persentase Mata Kuliah yang Menggunakan LMS secara Aktif',
                'definition' => 'Persentase mata kuliah yang memanfaatkan Learning Management System (e-learning) secara aktif (mengunggah materi, tugas, dan penilaian minimal 70% dari pertemuan).',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],

            // Standar 4 – Penilaian
            'penilaian_edom' => [
                'std_key' => 'penilaian', 'code' => 'IKU-4.1',
                'name' => 'Rata-Rata Skor EDOM (Evaluasi Dosen oleh Mahasiswa)',
                'definition' => 'Skor rata-rata evaluasi dosen oleh mahasiswa per semester menggunakan kuesioner baku LPMI dengan skala 1-4.',
                'measurement_type' => 'numeric', 'unit' => 'skala 4.0',
            ],
            'penilaian_tepat_waktu' => [
                'std_key' => 'penilaian', 'code' => 'IKT-4.2',
                'name' => 'Persentase Nilai yang Diserahkan Tepat Waktu',
                'definition' => 'Persentase dosen yang menyerahkan nilai akhir mahasiswa ke BAAK paling lambat 7 hari kerja setelah Ujian Akhir Semester (UAS) berakhir.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],

            // Standar 5 – Dosen & Tendik
            'dosen_s3' => [
                'std_key' => 'dosen', 'code' => 'IKU-5.1',
                'name' => 'Persentase Dosen Tetap Bergelar Doktor (S3)',
                'definition' => 'Persentase Dosen Tetap Program Studi (DTPS) yang memiliki kualifikasi akademik Doktor (S3) dari program studi yang relevan.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'dosen_rasio' => [
                'std_key' => 'dosen', 'code' => 'IKU-5.2',
                'name' => 'Rasio Dosen Tetap terhadap Mahasiswa Aktif',
                'definition' => 'Rasio antara jumlah Dosen Tetap Program Studi (DTPS) terhadap jumlah mahasiswa aktif yang dibimbing. Standar ideal: 1:25 s.d. 1:35.',
                'measurement_type' => 'numeric', 'unit' => '1:N',
            ],
            'dosen_lektor_kepala' => [
                'std_key' => 'dosen', 'code' => 'IKT-5.3',
                'name' => 'Persentase Dosen Berjabatan Lektor Kepala atau Guru Besar',
                'definition' => 'Persentase DTPS yang memiliki jabatan fungsional akademik Lektor Kepala (LK) atau Guru Besar (GB).',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'dosen_sertdik' => [
                'std_key' => 'dosen', 'code' => 'IKT-5.4',
                'name' => 'Persentase Dosen Bersertifikat Pendidik Profesional (Serdos)',
                'definition' => 'Persentase DTPS yang telah memiliki sertifikat pendidik profesional (serdos) dari Kemendikbudristek/Kemendiktisaintek.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],

            // Standar 6 – Sarpras
            'sarpras_lab_komputer' => [
                'std_key' => 'sarpras', 'code' => 'IKU-6.1',
                'name' => 'Rasio Komputer Laboratorium terhadap Mahasiswa per Sesi',
                'definition' => 'Rasio jumlah unit komputer yang berfungsi di laboratorium terhadap kapasitas mahasiswa per sesi praktikum. Standar: minimal 1 komputer per 2 mahasiswa.',
                'measurement_type' => 'numeric', 'unit' => '1:N',
            ],
            'sarpras_bandwidth' => [
                'std_key' => 'sarpras', 'code' => 'IKU-6.2',
                'name' => 'Bandwidth Internet per Mahasiswa Aktif',
                'definition' => 'Kapasitas bandwidth internet total kampus dibagi jumlah mahasiswa aktif. Standar minimal: 0.5 Kbps per mahasiswa aktif.',
                'measurement_type' => 'numeric', 'unit' => 'Kbps/mahasiswa',
            ],

            // Standar 7 – Pengelolaan
            'pengelolaan_rps' => [
                'std_key' => 'pengelolaan', 'code' => 'IKU-7.1',
                'name' => 'Persentase Mata Kuliah yang Memiliki RPS Tervalidasi',
                'definition' => 'Persentase mata kuliah yang memiliki RPS yang telah divalidasi dan ditandatangani oleh Ketua Program Studi sebelum perkuliahan dimulai.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'pengelolaan_monev' => [
                'std_key' => 'pengelolaan', 'code' => 'IKT-7.2',
                'name' => 'Frekuensi Monitoring dan Evaluasi Proses Pembelajaran per Semester',
                'definition' => 'Jumlah kali pelaksanaan monitoring dan evaluasi terstruktur terhadap pelaksanaan proses pembelajaran oleh LPMI per semester.',
                'measurement_type' => 'numeric', 'unit' => 'kali/semester',
            ],

            // Standar 8 – Pembiayaan
            'pembiayaan_oprasional' => [
                'std_key' => 'pembiayaan', 'code' => 'IKU-8.1',
                'name' => 'Biaya Operasional Pembelajaran per Mahasiswa per Semester',
                'definition' => 'Total biaya operasional pendidikan (diluar investasi) dibagi jumlah mahasiswa aktif per semester.',
                'measurement_type' => 'numeric', 'unit' => 'Rp juta/mahasiswa/semester',
            ],
            'pembiayaan_ristek' => [
                'std_key' => 'pembiayaan', 'code' => 'IKT-8.2',
                'name' => 'Persentase Alokasi Dana Penelitian dan PkM dari Total Anggaran',
                'definition' => 'Persentase dana yang dialokasikan untuk kegiatan penelitian dan pengabdian kepada masyarakat dari total anggaran institusi tahunan.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],

            // Standar 9 – Penelitian & PkM
            'riset_publikasi' => [
                'std_key' => 'penelitian_pkm', 'code' => 'IKU-9.1',
                'name' => 'Rata-Rata Publikasi Ilmiah per Dosen per Tahun',
                'definition' => 'Rata-rata jumlah artikel ilmiah yang dipublikasikan di jurnal nasional terakreditasi (SINTA 1-6) atau jurnal internasional bereputasi (Scopus/WoS) per dosen tetap per tahun.',
                'measurement_type' => 'numeric', 'unit' => 'artikel/dosen/tahun',
            ],
            'riset_hibah' => [
                'std_key' => 'penelitian_pkm', 'code' => 'IKT-9.2',
                'name' => 'Persentase Dosen yang Memperoleh Hibah Penelitian Eksternal',
                'definition' => 'Persentase dosen tetap yang berhasil memperoleh dana hibah penelitian eksternal (DRTPM Kemendikbudristek, LPDP, Industri, atau sumber lainnya) dalam satu tahun akademik.',
                'measurement_type' => 'percentage', 'unit' => '%',
            ],
            'pkm_kegiatan' => [
                'std_key' => 'penelitian_pkm', 'code' => 'IKU-9.3',
                'name' => 'Jumlah Kegiatan PkM yang Dilaksanakan Dosen per Tahun',
                'definition' => 'Total kegiatan pengabdian kepada masyarakat yang dilaksanakan oleh dosen tetap (baik terdanai internal maupun eksternal) dalam satu tahun akademik.',
                'measurement_type' => 'numeric', 'unit' => 'kegiatan/tahun',
            ],
        ];

        $result = [];
        foreach ($indicatorDefs as $key => $def) {
            $stdKey = $def['std_key'];
            unset($def['std_key']);

            $result[$key] = SpmiIndicator::query()->firstOrCreate(
                ['spmi_standard_id' => $standards[$stdKey]->getKey(), 'code' => $def['code']],
                array_merge($def, [
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'weight' => null,
                    'status' => 'active',
                    'validation_rules' => null,
                ]),
            );
        }

        return $result;
    }

    /**
     * @param array<string, SpmiIndicator> $indicators
     * @param array<string, User> $users
     */
    private function seedTargetDanRealisasi(
        array $indicators,
        PerguruanTinggi $pt,
        ProgramStudi $prodiTi,
        ProgramStudi $prodiSi,
        array $users,
    ): void {
        $periodYear = 2023;
        $periodCode = 'TA-2023-2024';

        // Data target dan realisasi realistis STMIK swasta menengah Indonesia
        // Format: [target_numeric, realisasi_ti, realisasi_si, keterangan_status]
        $targetData = [
            // Standar 1 – Kompetensi Lulusan
            'skl_cpl_review'        => ['target' => 1, 'real_ti' => 1, 'real_si' => 1],
            'skl_keselarasan_dudika' => ['target' => 75, 'real_ti' => 71.4, 'real_si' => 68.9],
            'skl_waktu_tunggu'      => ['target' => 6, 'real_ti' => 5.2, 'real_si' => 6.8],
            'skl_ipk_lulusan'       => ['target' => 3.30, 'real_ti' => 3.35, 'real_si' => 3.28],

            // Standar 2 – Isi Pembelajaran
            'isi_obe_coverage'      => ['target' => 80, 'real_ti' => 72, 'real_si' => 68],
            'isi_mbkm'              => ['target' => 30, 'real_ti' => 18.5, 'real_si' => 14.2],

            // Standar 3 – Proses Pembelajaran
            'proses_kehadiran_dosen' => ['target' => 95, 'real_ti' => 94.3, 'real_si' => 93.7],
            'proses_lms'             => ['target' => 70, 'real_ti' => 62.5, 'real_si' => 58.8],

            // Standar 4 – Penilaian
            'penilaian_edom'         => ['target' => 3.25, 'real_ti' => 3.18, 'real_si' => 3.22],
            'penilaian_tepat_waktu'  => ['target' => 90, 'real_ti' => 82.5, 'real_si' => 85.0],

            // Standar 5 – SDM Dosen (indikator level institusi, bukan per prodi)
            'dosen_s3'              => ['target' => 20, 'real_ti' => 17.6, 'real_si' => 15.4],
            'dosen_rasio'           => ['target' => 30, 'real_ti' => 33.2, 'real_si' => 35.8],
            'dosen_lektor_kepala'   => ['target' => 25, 'real_ti' => 23.5, 'real_si' => 20.0],
            'dosen_sertdik'         => ['target' => 70, 'real_ti' => 64.7, 'real_si' => 61.5],

            // Standar 6 – Sarpras
            'sarpras_lab_komputer'  => ['target' => 2, 'real_ti' => 1.8, 'real_si' => 2.1],
            'sarpras_bandwidth'     => ['target' => 0.5, 'real_ti' => 0.64, 'real_si' => 0.64],

            // Standar 7 – Pengelolaan
            'pengelolaan_rps'       => ['target' => 90, 'real_ti' => 86.4, 'real_si' => 83.0],
            'pengelolaan_monev'     => ['target' => 2, 'real_ti' => 2, 'real_si' => 2],

            // Standar 8 – Pembiayaan
            'pembiayaan_oprasional' => ['target' => 2.4, 'real_ti' => 2.65, 'real_si' => 2.55],
            'pembiayaan_ristek'     => ['target' => 10, 'real_ti' => 7.8, 'real_si' => 7.8],

            // Standar 9 – Penelitian & PkM
            'riset_publikasi'       => ['target' => 0.5, 'real_ti' => 0.32, 'real_si' => 0.28],
            'riset_hibah'           => ['target' => 30, 'real_ti' => 17.6, 'real_si' => 15.4],
            'pkm_kegiatan'          => ['target' => 20, 'real_ti' => 16, 'real_si' => 13],
        ];

        foreach ($targetData as $indicatorKey => $data) {
            if (! isset($indicators[$indicatorKey])) {
                continue;
            }

            $indicator = $indicators[$indicatorKey];

            // Buat target untuk Prodi TI
            $targetTi = SpmiTarget::query()->firstOrCreate(
                [
                    'spmi_indicator_id' => $indicator->getKey(),
                    'program_studi_id' => $prodiTi->getKey(),
                    'period_year' => $periodYear,
                    'period_code' => $periodCode,
                ],
                [
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'target_numeric' => $data['target'],
                    'status' => 'approved',
                    'set_by' => $users['kepala_lpmi']->getKey(),
                ],
            );

            // Buat target untuk Prodi SI
            $targetSi = SpmiTarget::query()->firstOrCreate(
                [
                    'spmi_indicator_id' => $indicator->getKey(),
                    'program_studi_id' => $prodiSi->getKey(),
                    'period_year' => $periodYear,
                    'period_code' => $periodCode,
                ],
                [
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'target_numeric' => $data['target'],
                    'status' => 'approved',
                    'set_by' => $users['kepala_lpmi']->getKey(),
                ],
            );

            // Buat realisasi Prodi TI
            $realTi = SpmiRealization::query()->firstOrCreate(
                [
                    'spmi_indicator_id' => $indicator->getKey(),
                    'program_studi_id' => $prodiTi->getKey(),
                    'period_year' => $periodYear,
                ],
                [
                    'spmi_target_id' => $targetTi->getKey(),
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'realization_numeric' => $data['real_ti'],
                    'source_type' => 'internal_report',
                    'source_reference' => 'Laporan Kinerja Program Studi TI TA 2023/2024',
                    'status' => 'verified',
                    'recorded_by' => $users['kaprodi_ti']->getKey(),
                    'verified_by' => $users['kepala_lpmi']->getKey(),
                    'verified_at' => '2024-07-15 10:00:00',
                ],
            );

            // Buat realisasi Prodi SI
            $realSi = SpmiRealization::query()->firstOrCreate(
                [
                    'spmi_indicator_id' => $indicator->getKey(),
                    'program_studi_id' => $prodiSi->getKey(),
                    'period_year' => $periodYear,
                ],
                [
                    'spmi_target_id' => $targetSi->getKey(),
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'realization_numeric' => $data['real_si'],
                    'source_type' => 'internal_report',
                    'source_reference' => 'Laporan Kinerja Program Studi SI TA 2023/2024',
                    'status' => 'verified',
                    'recorded_by' => $users['kaprodi_si']->getKey(),
                    'verified_by' => $users['kepala_lpmi']->getKey(),
                    'verified_at' => '2024-07-15 14:00:00',
                ],
            );

            // Buat evaluasi otomatis (SPMI Evaluation)
            $achieveTi = $data['target'] > 0 ? round(($data['real_ti'] / $data['target']) * 100, 2) : 100;
            $achieveSi = $data['target'] > 0 ? round(($data['real_si'] / $data['target']) * 100, 2) : 100;

            SpmiEvaluation::query()->firstOrCreate(
                ['spmi_realization_id' => $realTi->getKey()],
                [
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'program_studi_id' => $prodiTi->getKey(),
                    'result' => $achieveTi >= 100 ? 'met' : ($achieveTi >= 85 ? 'partially_met' : 'not_met'),
                    'achievement_percentage' => $achieveTi,
                    'analysis' => $achieveTi >= 100
                        ? "Indikator {$indicator->name} untuk Prodi TI tercapai dengan baik pada TA 2023/2024."
                        : "Indikator {$indicator->name} untuk Prodi TI belum mencapai target (realisasi {$achieveTi}%). Perlu tindak lanjut peningkatan.",
                    'root_cause' => $achieveTi < 100 ? 'Analisis akar masalah perlu dikaji lebih mendalam melalui rapat program studi.' : null,
                    'recommendation' => $achieveTi < 100 ? 'Perlu program perbaikan terstruktur dan monitoring berkala oleh LPMI.' : null,
                    'status' => 'approved',
                    'evaluated_by' => $users['kepala_lpmi']->getKey(),
                    'evaluated_at' => '2024-08-01 09:00:00',
                ],
            );

            SpmiEvaluation::query()->firstOrCreate(
                ['spmi_realization_id' => $realSi->getKey()],
                [
                    'perguruan_tinggi_id' => $pt->getKey(),
                    'program_studi_id' => $prodiSi->getKey(),
                    'result' => $achieveSi >= 100 ? 'met' : ($achieveSi >= 85 ? 'partially_met' : 'not_met'),
                    'achievement_percentage' => $achieveSi,
                    'analysis' => $achieveSi >= 100
                        ? "Indikator {$indicator->name} untuk Prodi SI tercapai dengan baik pada TA 2023/2024."
                        : "Indikator {$indicator->name} untuk Prodi SI belum mencapai target (realisasi {$achieveSi}%). Perlu tindak lanjut peningkatan.",
                    'root_cause' => $achieveSi < 100 ? 'Analisis akar masalah perlu dikaji lebih mendalam melalui rapat program studi.' : null,
                    'recommendation' => $achieveSi < 100 ? 'Perlu program perbaikan terstruktur dan monitoring berkala oleh LPMI.' : null,
                    'status' => 'approved',
                    'evaluated_by' => $users['kepala_lpmi']->getKey(),
                    'evaluated_at' => '2024-08-01 10:00:00',
                ],
            );
        }
    }

    /**
     * @param array<string, User> $users
     */
    private function seedAmiCycle(
        PerguruanTinggi $pt,
        ProgramStudi $prodiTi,
        ProgramStudi $prodiSi,
        array $users,
    ): AmiCycle {
        $cycle = AmiCycle::query()->firstOrCreate(
            ['perguruan_tinggi_id' => $pt->getKey(), 'code' => 'AMI-STMIK-NI-2024-GNP'],
            [
                'program_studi_id' => null,
                'instrument_version_id' => null,
                'name' => 'Audit Mutu Internal (AMI) STMIK Nusantara Informatika – Semester Genap 2023/2024',
                'period_year' => 2024,
                'scope_type' => 'institution',
                'status' => 'completed',
                'planned_start' => '2024-06-03',
                'planned_end' => '2024-06-28',
                'actual_start' => '2024-06-05',
                'actual_end' => '2024-06-26',
                'coordinator_id' => $users['kepala_lpmi']->getKey(),
            ],
        );

        // Assign Auditor
        $assignments = [
            ['user_id' => $users['kepala_lpmi']->getKey(), 'role' => 'lead_auditor'],
            ['user_id' => $users['auditor_1']->getKey(), 'role' => 'auditor'],
            ['user_id' => $users['auditor_2']->getKey(), 'role' => 'auditor'],
        ];

        foreach ($assignments as $assign) {
            AmiAssignment::query()->firstOrCreate(
                [
                    'ami_cycle_id' => $cycle->getKey(),
                    'user_id' => $assign['user_id'],
                    'assignment_role' => $assign['role'],
                ],
                [
                    'status' => 'accepted',
                    'accepted_at' => '2024-05-27 08:00:00',
                ],
            );
        }

        return $cycle;
    }

    /**
     * @param array<string, SpmiStandard> $standards
     * @param array<string, SpmiIndicator> $indicators
     */
    private function seedAmiChecklist(AmiCycle $cycle, array $standards, array $indicators): void
    {
        $checklistItems = [
            [
                'std_key' => 'dosen', 'ind_key' => 'dosen_s3',
                'code' => 'CHKL-001',
                'question' => 'Apakah persentase Dosen Tetap Program Studi (DTPS) yang bergelar Doktor (S3) sudah mencapai target standar minimal 20%?',
                'response_type' => 'numeric_score', 'sort_order' => 1,
                'response_status' => 'completed',
                'score' => 2.5,
                'response' => 'Prodi TI: 17.6%, Prodi SI: 15.4%. Rata-rata 16.5%, belum mencapai target standar 20%.',
                'auditor_notes' => 'Ditemukan bahwa 2 dosen sedang dalam proses studi S3 (belum selesai). Perlu akselerasi program beasiswa studi lanjut.',
                'evidence_required' => true,
            ],
            [
                'std_key' => 'dosen', 'ind_key' => 'dosen_rasio',
                'code' => 'CHKL-002',
                'question' => 'Apakah rasio dosen:mahasiswa aktif masih dalam batas standar yang ditetapkan (≤1:35)?',
                'response_type' => 'numeric_score', 'sort_order' => 2,
                'response_status' => 'completed',
                'score' => 2.0,
                'response' => 'Prodi TI: 1:33.2 (masih dalam batas), Prodi SI: 1:35.8 (melampaui batas standar 1:35).',
                'auditor_notes' => 'Prodi SI berpotensi mengalami kelebihan beban mengajar dosen jika tidak segera direkrut dosen tetap baru.',
                'evidence_required' => true,
            ],
            [
                'std_key' => 'penelitian_pkm', 'ind_key' => 'riset_publikasi',
                'code' => 'CHKL-003',
                'question' => 'Apakah rata-rata publikasi ilmiah dosen per tahun sudah mencapai target standar minimal 0.5 artikel/dosen/tahun?',
                'response_type' => 'numeric_score', 'sort_order' => 3,
                'response_status' => 'completed',
                'score' => 1.5,
                'response' => 'Rata-rata publikasi: TI 0.32 artikel/dosen/tahun, SI 0.28 artikel/dosen/tahun. Keduanya jauh di bawah target 0.5.',
                'auditor_notes' => 'Mayoritas dosen hanya menerbitkan di jurnal nasional SINTA 5-6, belum ada yang di SINTA 1-2 atau Scopus. Perlu program mentoring penulisan artikel ilmiah.',
                'evidence_required' => true,
            ],
            [
                'std_key' => 'proses', 'ind_key' => 'proses_lms',
                'code' => 'CHKL-004',
                'question' => 'Apakah lebih dari 70% mata kuliah memanfaatkan LMS secara aktif dalam proses pembelajaran?',
                'response_type' => 'numeric_score', 'sort_order' => 4,
                'response_status' => 'completed',
                'score' => 2.5,
                'response' => 'Penggunaan aktif LMS: TI 62.5%, SI 58.8%. Masih di bawah target 70%.',
                'auditor_notes' => 'Beberapa dosen senior belum terbiasa menggunakan LMS. Perlu pelatihan dan pendampingan lebih intensif dari UPT TIK.',
                'evidence_required' => false,
            ],
            [
                'std_key' => 'pengelolaan', 'ind_key' => 'pengelolaan_rps',
                'code' => 'CHKL-005',
                'question' => 'Apakah lebih dari 90% mata kuliah memiliki RPS yang telah divalidasi Ketua Program Studi sebelum perkuliahan dimulai?',
                'response_type' => 'numeric_score', 'sort_order' => 5,
                'response_status' => 'completed',
                'score' => 3.0,
                'response' => 'RPS tervalidasi: TI 86.4%, SI 83.0%. Belum mencapai target 90%, namun proses berjalan membaik dari semester sebelumnya.',
                'auditor_notes' => 'Perlu mekanisme otomasi pengingat (reminder) melalui sistem untuk mendorong dosen menyerahkan RPS lebih awal.',
                'evidence_required' => false,
            ],
        ];

        foreach ($checklistItems as $item) {
            $stdKey = $item['std_key'];
            $indKey = $item['ind_key'];
            $stdId = null;
            $indId = null;

            unset($item['std_key'], $item['ind_key']);

            if (isset($standards[$stdKey])) {
                $stdId = $standards[$stdKey]->getKey();
            }
            if (isset($indicators[$indKey])) {
                $indId = $indicators[$indKey]->getKey();
            }

            AmiChecklistItem::query()->firstOrCreate(
                ['ami_cycle_id' => $cycle->getKey(), 'code' => $item['code']],
                array_merge($item, [
                    'spmi_standard_id' => $stdId,
                    'spmi_indicator_id' => $indId,
                    'instrument_node_id' => null,
                ]),
            );
        }
    }

    /**
     * @param array<string, User> $users
     * @return array<string, AmiFinding>
     */
    private function seedAmiFindings(AmiCycle $cycle, array $users): array
    {
        $findingDefs = [
            'finding_s3' => [
                'code' => 'TMN-001',
                'classification' => 'major_nc',
                'severity' => 'high',
                'condition' => 'Persentase DTPS bergelar Doktor (S3) masih 17.6% (Prodi TI) dan 15.4% (Prodi SI), di bawah target standar SPMI 20%.',
                'requirement' => 'Standar 5 SPMI: Persentase DTPS bergelar S3 minimal 20%.',
                'criteria' => 'SN-Dikti Pasal 16 ayat (2) dan Standar 5 SPMI STMIK Nusantara Informatika versi 2.0.',
                'cause' => '(1) Tidak ada skema beasiswa studi lanjut S3 yang sistematis dari institusi. (2) Dosen enggan melanjutkan S3 karena beban mengajar tinggi. (3) Anggaran beasiswa studi lanjut tidak diprioritaskan dalam RKAT.',
                'impact' => 'Berisiko menurunkan peringkat akreditasi LAM INFOKOM dan BAN-PT. Menghambat karir dosen menuju jabatan Lektor Kepala dan Guru Besar.',
                'recommendation' => 'Institusi segera menetapkan kebijakan beasiswa studi lanjut S3 bagi dosen (minimal 2 dosen per tahun), memberikan keringanan beban mengajar bagi dosen studi lanjut, dan mengalokasikan anggaran beasiswa dalam RKAT 2024/2025.',
                'status' => 'closed',
            ],
            'finding_rasio_si' => [
                'code' => 'TMN-002',
                'classification' => 'major_nc',
                'severity' => 'high',
                'condition' => 'Rasio dosen:mahasiswa aktif Prodi SI mencapai 1:35.8, melampaui batas standar 1:35 yang ditetapkan SPMI.',
                'requirement' => 'Standar 5 SPMI: Rasio dosen tetap terhadap mahasiswa aktif tidak melebihi 1:35.',
                'criteria' => 'SN-Dikti Pasal 16 ayat (3) dan Standar 5 SPMI STMIK Nusantara Informatika versi 2.0.',
                'cause' => 'Pertumbuhan mahasiswa baru Prodi SI yang cukup signifikan (+12% YoY) tidak diimbangi dengan rekrutmen dosen tetap baru.',
                'impact' => 'Kualitas bimbingan akademik mahasiswa berpotensi menurun. Dosen Prodi SI menanggung beban SKS mengajar yang melebihi batas normal (>12 SKS/semester).',
                'recommendation' => 'Membuka lowongan dosen tetap Prodi SI minimal 2 orang pada semester gasal 2024/2025 sesuai kebutuhan bidang ilmu (Sistem Informasi/Manajemen Informatika). Membatasi kuota PMB baru Prodi SI jika rekrutmen dosen terhambat.',
                'status' => 'in_progress',
            ],
            'finding_publikasi' => [
                'code' => 'TMN-003',
                'classification' => 'major_nc',
                'severity' => 'high',
                'condition' => 'Rata-rata publikasi ilmiah dosen jauh di bawah target: Prodi TI 0.32 artikel/dosen/tahun, Prodi SI 0.28 artikel/dosen/tahun. Target standar: 0.5 artikel/dosen/tahun.',
                'requirement' => 'Standar 9 SPMI: Rata-rata publikasi ilmiah dosen minimal 0.5 artikel/dosen/tahun di jurnal nasional terakreditasi SINTA atau internasional bereputasi.',
                'criteria' => 'Standar 9 SPMI STMIK Nusantara Informatika versi 2.0 dan SN-Dikti Pasal 24.',
                'cause' => '(1) Tidak ada program insentif publikasi yang memadai. (2) Tidak ada kelompok riset (Research Group) yang terstruktur. (3) Mayoritas dosen tidak memiliki peta jalan penelitian (roadmap riset) personal. (4) Anggaran hibah penelitian internal sangat kecil (rata-rata Rp 3 juta/dosen).',
                'impact' => 'Skor butir penelitian pada instrumen akreditasi LAM INFOKOM dan BAN-PT akan sangat rendah. Memengaruhi reputasi ilmiah institusi.',
                'recommendation' => '(1) Menetapkan program insentif publikasi: bonus Rp 1-2 juta/artikel SINTA 3-6, Rp 3-5 juta/artikel SINTA 1-2, Rp 5-10 juta/artikel Scopus. (2) Membentuk Research Group bidang AI, Data Science, Cybersecurity, dan IS. (3) Meningkatkan anggaran hibah penelitian internal minimal Rp 10 juta/dosen/tahun. (4) Menyelenggarakan workshop penulisan artikel ilmiah bersama jurnal mitra.',
                'status' => 'in_progress',
            ],
            'finding_lms' => [
                'code' => 'TMN-004',
                'classification' => 'minor_nc',
                'severity' => 'medium',
                'condition' => 'Penggunaan aktif LMS untuk perkuliahan masih di bawah target: Prodi TI 62.5%, Prodi SI 58.8% (target: 70%).',
                'requirement' => 'Standar 3 SPMI: Minimal 70% mata kuliah memanfaatkan LMS secara aktif.',
                'criteria' => 'Standar 3 SPMI STMIK Nusantara Informatika versi 2.0.',
                'cause' => 'Dosen senior (di atas 50 tahun) kurang terbiasa menggunakan platform digital. Tidak ada kewajiban tertulis dari pimpinan dan sanksi yang jelas bagi dosen yang tidak menggunakan LMS.',
                'impact' => 'Pembelajaran menjadi kurang fleksibel dan terdigitalisasi. Data aktivitas belajar mahasiswa tidak terekam secara menyeluruh.',
                'recommendation' => '(1) UPT TIK menyelenggarakan pelatihan LMS wajib bagi seluruh dosen di awal semester (minimal 1x/semester). (2) Menerbitkan Surat Edaran Ketua STMIK yang mewajibkan penggunaan LMS minimal 70% pertemuan/semester. (3) Monitoring kepatuhan LMS masuk dalam penilaian kinerja dosen (BKD).',
                'status' => 'closed',
            ],
            'finding_rps' => [
                'code' => 'TMN-005',
                'classification' => 'observasi',
                'severity' => 'low',
                'condition' => 'Masih terdapat 13.6% (Prodi TI) dan 17% (Prodi SI) mata kuliah yang belum memiliki RPS tervalidasi tepat waktu sebelum perkuliahan dimulai.',
                'requirement' => 'Standar 7 SPMI: Minimal 90% mata kuliah memiliki RPS tervalidasi sebelum perkuliahan dimulai.',
                'criteria' => 'Standar 7 SPMI STMIK Nusantara Informatika versi 2.0.',
                'cause' => 'Belum ada sistem reminder otomatis untuk pengumpulan RPS. Ketua Program Studi kesulitan memantau status pengumpulan secara manual.',
                'impact' => 'Kecil – perkuliahan tetap berjalan namun administrasi akademik kurang tertib.',
                'recommendation' => 'Mengaktifkan fitur reminder otomatis di Sistem Informasi Akademik (SIAKAD) untuk notifikasi pengumpulan RPS 2 minggu sebelum perkuliahan dimulai. Target pencapaian ditingkatkan ke 95% pada TA 2024/2025.',
                'status' => 'closed',
            ],
        ];

        $result = [];
        foreach ($findingDefs as $key => $def) {
            $result[$key] = AmiFinding::query()->firstOrCreate(
                ['ami_cycle_id' => $cycle->getKey(), 'code' => $def['code']],
                array_merge($def, [
                    'ami_checklist_item_id' => null,
                    'reported_by' => $key === 'finding_lms' || $key === 'finding_rps'
                        ? $this->getAuditorId('auditor_2', $cycle)
                        : $this->getAuditorId('auditor_1', $cycle),
                ]),
            );
        }

        return $result;
    }

    private function getAuditorId(string $role, AmiCycle $cycle): int
    {
        return $cycle->assignments()
            ->whereHas('user', fn ($q) => $q->where('email', $role.'@stmik-nusantara.demo'))
            ->value('user_id') ?? 1;
    }

    /**
     * @param array<string, User> $users
     * @param array<string, AmiFinding> $findings
     * @return array{meeting: RtmMeeting, decisions: array<string, RtmDecision>}
     */
    private function seedRtm(
        PerguruanTinggi $pt,
        AmiCycle $amiCycle,
        array $users,
        array $findings,
    ): array {
        $meeting = RtmMeeting::query()->firstOrCreate(
            ['perguruan_tinggi_id' => $pt->getKey(), 'code' => 'RTM-STMIK-NI-2024-GNP'],
            [
                'program_studi_id' => null,
                'ami_cycle_id' => $amiCycle->getKey(),
                'title' => 'Rapat Tinjauan Manajemen (RTM) Semester Genap 2023/2024 – STMIK Nusantara Informatika',
                'held_at' => '2024-07-09 09:00:00',
                'status' => 'completed',
                'minutes' => "RTM Semester Genap 2023/2024 STMIK Nusantara Informatika diselenggarakan pada Selasa, 9 Juli 2024, pukul 09.00-13.00 WIB di Ruang Sidang Rektorat. Rapat dipimpin oleh Ketua STMIK (Dr. Ir. Agung Wibowo, M.Kom.) dan dihadiri oleh Kepala LPMI, para Ketua Program Studi, Kepala Biro Akademik, dan Kepala UPT TIK.\n\nAgenda rapat:\n1. Paparan hasil AMI Semester Genap 2023/2024 oleh Kepala LPMI.\n2. Analisis dan pembahasan 5 temuan AMI (3 Major NC, 1 Minor NC, 1 Observasi).\n3. Penetapan Rencana Tindak Lanjut (RTL) untuk setiap temuan.\n4. Penandatanganan Berita Acara RTM.\n\nRapat berjalan kondusif dan seluruh keputusan disetujui secara musyawarah mufakat.",
                'chair_id' => $users['ketua_stmik']->getKey(),
            ],
        );

        // Peserta RTM
        $participants = [
            [$users['ketua_stmik']->getKey(), 'Ketua STMIK (Pimpinan Rapat)'],
            [$users['kepala_lpmi']->getKey(), 'Kepala LPMI (Pemapar Hasil AMI)'],
            [$users['auditor_1']->getKey(), 'Auditor Internal'],
            [$users['auditor_2']->getKey(), 'Auditor Internal'],
            [$users['kaprodi_ti']->getKey(), 'Ketua Program Studi TI (Teraudit)'],
            [$users['kaprodi_si']->getKey(), 'Ketua Program Studi SI (Teraudit)'],
        ];

        foreach ($participants as [$userId, $role]) {
            RtmParticipant::query()->firstOrCreate(
                ['rtm_meeting_id' => $meeting->getKey(), 'user_id' => $userId],
                ['role' => $role, 'attended' => true],
            );
        }

        // Keputusan RTM
        $decisionDefs = [
            'dec_s3' => [
                'code' => 'KPTS-RTM-001',
                'finding_key' => 'finding_s3',
                'decision' => 'Menyetujui program Beasiswa Studi Lanjut S3 bagi dosen tetap STMIK Nusantara Informatika mulai Semester Gasal 2024/2025 dengan kuota 2 dosen/tahun dan besaran beasiswa Rp 15 juta/tahun. Kepala LPMI berkoordinasi dengan Wakil Ketua II (Bid. Keuangan) untuk memasukkan anggaran beasiswa dalam RKAT 2024/2025.',
                'rationale' => 'Merespons temuan Major NC TMN-001 terkait rendahnya persentase dosen S3. Target: persentase dosen S3 meningkat ke 22% pada TA 2024/2025.',
            ],
            'dec_rasio_si' => [
                'code' => 'KPTS-RTM-002',
                'finding_key' => 'finding_rasio_si',
                'decision' => 'Membuka lowongan Dosen Tetap Prodi Sistem Informasi minimal 2 orang pada bulan Agustus-September 2024. Membatasi kuota PMB baru Prodi SI maksimal 60 mahasiswa hingga rasio dosen:mahasiswa kembali ke 1:32.',
                'rationale' => 'Merespons temuan Major NC TMN-002 rasio dosen:mahasiswa Prodi SI yang melampaui batas standar (1:35.8).',
            ],
            'dec_publikasi' => [
                'code' => 'KPTS-RTM-003',
                'finding_key' => 'finding_publikasi',
                'decision' => 'Menetapkan program insentif publikasi ilmiah dengan skema: SINTA 5-6 Rp 1 juta/artikel, SINTA 3-4 Rp 2 juta/artikel, SINTA 1-2 Rp 4 juta/artikel, Scopus Q3-Q4 Rp 6 juta/artikel, Scopus Q1-Q2 Rp 10 juta/artikel. Membentuk Research Group pertama (RG Kecerdasan Artifisial dan Data Science) dengan Ketua RG yang ditunjuk. Meningkatkan anggaran hibah penelitian internal menjadi Rp 10 juta/dosen/tahun.',
                'rationale' => 'Merespons temuan Major NC TMN-003 rendahnya produktivitas publikasi ilmiah dosen.',
            ],
            'dec_lms' => [
                'code' => 'KPTS-RTM-004',
                'finding_key' => 'finding_lms',
                'decision' => 'Menerbitkan Surat Edaran Ketua STMIK yang mewajibkan seluruh dosen menggunakan LMS minimal 70% pertemuan per semester. UPT TIK menyelenggarakan pelatihan LMS wajib di awal semester gasal 2024/2025. Kepatuhan LMS dimasukkan ke dalam komponen penilaian Beban Kerja Dosen (BKD).',
                'rationale' => 'Merespons temuan Minor NC TMN-004 terkait rendahnya pemanfaatan LMS.',
            ],
            'dec_rps' => [
                'code' => 'KPTS-RTM-005',
                'finding_key' => 'finding_rps',
                'decision' => 'BAAK mengaktifkan fitur reminder otomatis di SIAKAD untuk pengumpulan RPS, dengan notifikasi H-14, H-7, dan H-1 sebelum perkuliahan dimulai. Target ketercapaian RPS tervalidasi ditingkatkan ke 95% pada TA 2024/2025.',
                'rationale' => 'Merespons temuan Observasi TMN-005 terkait keterlambatan pengumpulan RPS.',
            ],
            'dec_umum' => [
                'code' => 'KPTS-RTM-006',
                'finding_key' => null,
                'decision' => 'Menetapkan jadwal AMI semester gasal 2024/2025 pada bulan Desember 2024. Kepala LPMI menyiapkan instrumen AMI yang telah diperbarui berdasarkan evaluasi pelaksanaan AMI Semester Genap 2023/2024 paling lambat Oktober 2024.',
                'rationale' => 'Keputusan umum berdasarkan arahan pimpinan untuk keberlangsungan siklus PPEPP.',
            ],
        ];

        $decisions = [];
        foreach ($decisionDefs as $key => $def) {
            $findingId = isset($def['finding_key'], $findings[$def['finding_key']])
                ? $findings[$def['finding_key']]->getKey()
                : null;

            $decisions[$key] = RtmDecision::query()->firstOrCreate(
                ['rtm_meeting_id' => $meeting->getKey(), 'code' => $def['code']],
                [
                    'ami_finding_id' => $findingId,
                    'decision' => $def['decision'],
                    'rationale' => $def['rationale'],
                    'status' => 'approved',
                ],
            );
        }

        return ['meeting' => $meeting, 'decisions' => $decisions];
    }

    /**
     * @param array<string, AmiFinding> $findings
     * @param array<string, RtmDecision> $decisions
     * @param array<string, User> $users
     */
    private function seedRtlActions(
        PerguruanTinggi $pt,
        ProgramStudi $prodiTi,
        ProgramStudi $prodiSi,
        array $findings,
        array $decisions,
        array $users,
    ): void {
        $rtlDefs = [
            [
                'code' => 'RTL-2024-001',
                'title' => 'Program Beasiswa Studi Lanjut S3 bagi Dosen Tetap',
                'finding_key' => 'finding_s3',
                'decision_key' => 'dec_s3',
                'prodi_id' => null,
                'action_plan' => "1. Wakil Ketua II menyiapkan anggaran Rp 30 juta/tahun untuk beasiswa S3 (2 dosen × Rp 15 juta) dalam RKAT 2024/2025 (Agustus 2024).\n2. Kepala LPMI menyusun SK Ketua STMIK tentang Program Beasiswa Studi Lanjut S3 (September 2024).\n3. Kaprodi TI dan SI menginventarisasi dosen yang berminat melanjutkan S3 (September 2024).\n4. Seleksi dan penetapan penerima beasiswa oleh Senat STMIK (Oktober 2024).\n5. Monitoring kemajuan studi penerima beasiswa setiap semester.",
                'owner_key' => 'ketua_stmik',
                'due_date' => '2024-10-31',
                'progress_percent' => 100,
                'status' => 'closed',
                'evidence_of_completion' => 'SK Ketua STMIK No. 145/SK/STMIK-NI/X/2024 tentang Program Beasiswa Studi Lanjut S3 telah diterbitkan. 2 dosen ditetapkan sebagai penerima beasiswa untuk tahun akademik 2024/2025.',
            ],
            [
                'code' => 'RTL-2024-002',
                'title' => 'Rekrutmen Dosen Tetap Prodi Sistem Informasi',
                'finding_key' => 'finding_rasio_si',
                'decision_key' => 'dec_rasio_si',
                'prodi_id' => $prodiSi->getKey(),
                'action_plan' => "1. Wakil Ketua I (Bid. Akademik) mengusulkan formasi kebutuhan dosen SI ke Yayasan (Agustus 2024).\n2. Biro SDM menyiapkan pengumuman lowongan dosen tetap dan persyaratan (Agustus 2024).\n3. Seleksi administratif, tes tertulis, dan wawancara calon dosen (September–Oktober 2024).\n4. Penetapan dan penandatanganan kontrak dosen tetap baru (November 2024).\n5. Induction program dosen baru oleh LPMI (Desember 2024).",
                'owner_key' => 'kaprodi_si',
                'due_date' => '2024-11-30',
                'progress_percent' => 60,
                'status' => 'in_progress',
                'evidence_of_completion' => null,
            ],
            [
                'code' => 'RTL-2024-003',
                'title' => 'Program Insentif Publikasi & Pembentukan Research Group',
                'finding_key' => 'finding_publikasi',
                'decision_key' => 'dec_publikasi',
                'prodi_id' => null,
                'action_plan' => "1. Kepala LPMI menyusun draf SK Insentif Publikasi Ilmiah beserta mekanisme pengajuan klaim (Agustus 2024).\n2. Ketua STMIK menandatangani SK Insentif Publikasi (September 2024).\n3. Pembentukan Research Group (RG) Kecerdasan Artifisial dan Data Science dengan SK resmi (September 2024).\n4. Workshop penulisan artikel ilmiah dan perkenalan jurnal mitra (Oktober 2024).\n5. Peningkatan anggaran hibah penelitian internal Rp 10 juta/dosen melalui revisi RKAT.\n6. Monitoring produktivitas penelitian oleh LPMI setiap semester.",
                'owner_key' => 'kepala_lpmi',
                'due_date' => '2024-10-31',
                'progress_percent' => 45,
                'status' => 'in_progress',
                'evidence_of_completion' => null,
            ],
            [
                'code' => 'RTL-2024-004',
                'title' => 'Pelatihan LMS Wajib & Kebijakan Penggunaan E-Learning',
                'finding_key' => 'finding_lms',
                'decision_key' => 'dec_lms',
                'prodi_id' => null,
                'action_plan' => "1. UPT TIK menyelenggarakan pelatihan LMS wajib bagi seluruh dosen (Agustus 2024).\n2. Kepala LPMI menyiapkan draf Surat Edaran kewajiban penggunaan LMS (Agustus 2024).\n3. Ketua STMIK menandatangani Surat Edaran (September 2024).\n4. UPT TIK menyediakan helpdesk khusus LMS untuk membantu dosen (September 2024).\n5. LPMI memantau persentase penggunaan LMS di pertengahan semester (November 2024).",
                'owner_key' => 'kepala_lpmi',
                'due_date' => '2024-09-30',
                'progress_percent' => 100,
                'status' => 'closed',
                'evidence_of_completion' => 'Pelatihan LMS telah dilaksanakan pada 19-20 Agustus 2024 dengan 32 dosen hadir (100% dari target). SE Kewajiban Penggunaan LMS No. 088/SE/STMIK-NI/IX/2024 telah diterbitkan dan disosialisasikan. Persentase LMS aktif meningkat ke 74.5% (melebihi target 70%).',
            ],
            [
                'code' => 'RTL-2024-005',
                'title' => 'Aktivasi Reminder Otomatis Pengumpulan RPS di SIAKAD',
                'finding_key' => 'finding_rps',
                'decision_key' => 'dec_rps',
                'prodi_id' => null,
                'action_plan' => "1. UPT TIK mengembangkan dan mengaktifkan fitur reminder otomatis pengumpulan RPS di SIAKAD (Agustus 2024).\n2. Pengujian fitur reminder oleh tim BAAK (Agustus 2024).\n3. Sosialisasi kepada Kaprodi dan dosen melalui rapat prodi (Agustus 2024).\n4. Evaluasi efektivitas reminder menjelang semester gasal 2024/2025 (September 2024).",
                'owner_key' => 'kepala_lpmi',
                'due_date' => '2024-09-15',
                'progress_percent' => 100,
                'status' => 'closed',
                'evidence_of_completion' => 'Fitur reminder RPS otomatis berhasil diaktifkan di SIAKAD sejak 25 Agustus 2024. Tingkat pengumpulan RPS tepat waktu pada semester gasal 2024/2025 meningkat ke 92.3% (melampaui target 90%).',
            ],
        ];

        foreach ($rtlDefs as $def) {
            $findingId = isset($def['finding_key'], $findings[$def['finding_key']])
                ? $findings[$def['finding_key']]->getKey()
                : null;

            $decisionId = isset($def['decision_key'], $decisions[$def['decision_key']])
                ? $decisions[$def['decision_key']]->getKey()
                : null;

            $ownerId = isset($users[$def['owner_key']]) ? $users[$def['owner_key']]->getKey() : null;

            RtlAction::query()->firstOrCreate(
                ['perguruan_tinggi_id' => $pt->getKey(), 'code' => $def['code']],
                [
                    'program_studi_id' => $def['prodi_id'],
                    'ami_finding_id' => $findingId,
                    'rtm_decision_id' => $decisionId,
                    'owner_id' => $ownerId,
                    'title' => $def['title'],
                    'action_plan' => $def['action_plan'],
                    'due_date' => $def['due_date'],
                    'progress_percent' => $def['progress_percent'],
                    'status' => $def['status'],
                    'evidence_of_completion' => $def['evidence_of_completion'],
                    'verified_by' => $def['status'] === 'closed' ? $users['kepala_lpmi']->getKey() : null,
                    'verified_at' => $def['status'] === 'closed' ? now() : null,
                ],
            );
        }
    }
}
