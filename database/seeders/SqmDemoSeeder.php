<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accreditation;
use App\Models\AccreditationBody;
use App\Models\Document;
use App\Models\Evidence;
use App\Models\EvidenceVersion;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
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
use Illuminate\Support\Facades\Hash;

final class SqmDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');
        $foundation = Yayasan::query()->firstOrCreate(['kode' => 'YYS-DEMO'], ['nama' => 'Yayasan Demo Mutu']);
        $pt = PerguruanTinggi::query()->firstOrCreate(['kode_pt' => 'PT-DEMO'], ['yayasan_id' => $foundation->id, 'nama_pt' => 'Universitas Demo Mutu', 'jenis' => 'universitas', 'status' => 'active']);
        $pt2 = PerguruanTinggi::query()->firstOrCreate(['kode_pt' => 'PT-DEMO-2'], ['yayasan_id' => $foundation->id, 'nama_pt' => 'Institut Demo Mutu', 'jenis' => 'institut', 'status' => 'active']);
        $prodi = ProgramStudi::query()->firstOrCreate(['kode_prodi' => 'TI-DEMO'], ['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'S1 Teknologi Informasi', 'jenjang' => 'S1', 'status' => 'active']);

        $admin = User::query()->firstOrCreate(['email' => 'admin.demo@sqm.test'], ['name' => 'Administrator Demo', 'password' => $password, 'yayasan_id' => $foundation->id, 'perguruan_tinggi_id' => $pt->id, 'email_verified_at' => now()]);
        $quality = User::query()->firstOrCreate(['email' => 'mutu.demo@sqm.test'], ['name' => 'Pengelola Mutu Demo', 'password' => $password, 'yayasan_id' => $foundation->id, 'perguruan_tinggi_id' => $pt->id, 'email_verified_at' => now()]);
        $admin->assignRole('pt_admin');
        $quality->assignRole('quality_manager');

        $framework = SpmiFramework::query()->firstOrCreate(['perguruan_tinggi_id' => $pt->id, 'code' => 'SPMI-DEMO'], ['name' => 'Kerangka SPMI Demo', 'version_label' => '2026.1', 'status' => 'active', 'effective_from' => '2026-01-01']);
        $standard = SpmiStandard::query()->firstOrCreate(['spmi_framework_id' => $framework->id, 'code' => 'STD-01'], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'name' => 'Standar Pendidikan', 'statement' => 'Mutu pembelajaran memenuhi target institusi.', 'basis' => 'SPMI', 'status' => 'active', 'sort_order' => 1]);
        $indicator = SpmiIndicator::query()->firstOrCreate(['spmi_standard_id' => $standard->id, 'code' => 'IND-01'], ['perguruan_tinggi_id' => $pt->id, 'name' => 'Kepuasan mahasiswa', 'definition' => 'Persentase mahasiswa yang menyatakan puas.', 'measurement_type' => 'numeric', 'unit' => 'persen', 'weight' => 100, 'status' => 'active']);
        $target = SpmiTarget::query()->firstOrCreate(['spmi_indicator_id' => $indicator->id, 'program_studi_id' => $prodi->id, 'period_year' => 2026, 'period_code' => 'TA-2026'], ['perguruan_tinggi_id' => $pt->id, 'target_numeric' => 85, 'status' => 'approved', 'set_by' => $quality->id]);
        $realization = SpmiRealization::query()->firstOrCreate(['spmi_target_id' => $target->id, 'spmi_indicator_id' => $indicator->id, 'period_year' => 2026], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'realization_numeric' => 78, 'source_type' => 'survei', 'status' => 'verified', 'recorded_by' => $quality->id, 'verified_by' => $quality->id, 'verified_at' => now(), 'verification_notes' => 'Data demo telah diverifikasi.']);
        $evaluation = SpmiEvaluation::query()->firstOrCreate(['spmi_realization_id' => $realization->id], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'result' => 'partially_met', 'achievement_percentage' => 91.7647, 'analysis' => 'Capaian mendekati target.', 'root_cause' => 'Konsistensi layanan belum merata.', 'recommendation' => 'Perkuat tindak lanjut hasil survei.', 'status' => 'completed', 'evaluated_by' => $quality->id, 'evaluated_at' => now()]);
        SpmiImprovementProgram::query()->firstOrCreate(['perguruan_tinggi_id' => $pt->id, 'code' => 'RTL-SPMI-001'], ['spmi_evaluation_id' => $evaluation->id, 'spmi_indicator_id' => $indicator->id, 'spmi_target_id' => $target->id, 'program_studi_id' => $prodi->id, 'title' => 'Peningkatan tindak lanjut survei mahasiswa', 'action_plan' => 'Membuat rapat evaluasi dan monitoring per bulan.', 'owner_id' => $quality->id, 'due_date' => '2026-12-15', 'progress_percent' => 75, 'status' => 'completed', 'completion_notes' => 'Implementasi demo siap diverifikasi.']);

        $body = AccreditationBody::query()->firstOrCreate(['code' => 'BAN-PT'], ['name' => 'Badan Akreditasi Nasional Perguruan Tinggi', 'kind' => 'BAN-PT', 'status' => 'active']);
        $family = InstrumentFamily::query()->firstOrCreate(['accreditation_body_id' => $body->id, 'code' => 'IAPT-DEMO'], ['name' => 'Instrumen Akreditasi Perguruan Tinggi Demo', 'scope_type' => 'institution', 'description' => 'Instrumen demo untuk pengujian lokal.']);
        $version = InstrumentVersion::query()->firstOrCreate(['instrument_family_id' => $family->id, 'version_label' => 'Demo 1.0'], ['status' => 'published', 'source_reference' => 'Data demo internal', 'effective_from' => '2026-01-01', 'content_hash' => hash('sha256', 'IAPT-DEMO-1.0'), 'published_at' => now(), 'published_by' => $quality->id]);
        $accreditation = Accreditation::query()->firstOrCreate(['perguruan_tinggi_id' => $pt->id, 'code' => 'AKR-DEMO-2026'], ['program_studi_id' => null, 'instrument_version_id' => $version->id, 'scope_type' => 'institution', 'title' => 'Akreditasi Institusi Demo 2026', 'status' => 'readiness', 'planned_submission_date' => '2026-12-20', 'owner_id' => $quality->id]);

        $document = Document::query()->firstOrCreate(['disk' => 'google_drive', 'storage_path' => 'demo/led-2026'], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'uploaded_by' => $quality->id, 'external_url' => 'https://drive.google.com/drive/folders/demo-led-2026', 'external_file_id' => 'demo-led-2026', 'external_folder_url' => 'https://drive.google.com/drive/folders/demo-led-2026', 'link_access_mode' => 'anyone_with_link', 'original_name' => 'LED Demo 2026', 'mime_type' => 'application/pdf', 'size_bytes' => 0, 'sha256' => hash('sha256', 'external-demo-link'), 'visibility' => 'private', 'status' => 'active']);
        $evidence = Evidence::query()->firstOrCreate(['perguruan_tinggi_id' => $pt->id, 'code' => 'EVD-DEMO-001'], ['program_studi_id' => $prodi->id, 'created_by' => $quality->id, 'title' => 'Laporan survei kepuasan mahasiswa 2026', 'description' => 'Bukti demo berbasis tautan Google Drive.', 'valid_from' => '2026-01-01', 'valid_until' => '2026-12-31', 'status' => 'accepted', 'verified_by' => $quality->id, 'verified_at' => now()]);
        EvidenceVersion::query()->firstOrCreate(['evidence_id' => $evidence->id, 'version_no' => 1], ['document_id' => $document->id, 'created_by' => $quality->id, 'change_reason' => 'Versi awal data demo', 'manifest_hash' => hash('sha256', 'demo-evidence-v1'), 'locked_at' => now()]);
    }
}
