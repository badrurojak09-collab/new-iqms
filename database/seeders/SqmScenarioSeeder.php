<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Accreditation;
use App\Models\AccreditationReadinessItem;
use App\Models\AccreditationResponse;
use App\Models\AccreditationSection;
use App\Models\AmiChecklistItem;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\DocumentArtifact;
use App\Models\DocumentDefinition;
use App\Models\DocumentEvidenceReference;
use App\Models\DocumentGenerationRequest;
use App\Models\DocumentSnapshot;
use App\Models\DocumentTemplateVersion;
use App\Models\InstrumentNode;
use App\Models\RtlAction;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\ReadinessGap;
use App\Models\ReadinessResult;
use App\Models\ReadinessRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

final class SqmScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, SqmDemoSeeder::class]);
        $pt = \App\Models\PerguruanTinggi::where('kode_pt', 'PT-DEMO')->firstOrFail();
        $prodi = \App\Models\ProgramStudi::where('kode_prodi', 'TI-DEMO')->firstOrFail();
        $quality = User::where('email', 'mutu.demo@sqm.test')->firstOrFail();
        $version = \App\Models\InstrumentVersion::where('version_label', 'Demo 1.0')->firstOrFail();
        $accreditation = Accreditation::where('code', 'AKR-DEMO-2026')->firstOrFail();

        $ami = AmiCycle::firstOrCreate(['code' => 'AMI-DEMO-2026'], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'instrument_version_id' => $version->id, 'name' => 'Siklus AMI Demo 2026', 'period_year' => 2026, 'scope_type' => 'program_study', 'status' => 'completed', 'planned_start' => '2026-02-01', 'planned_end' => '2026-03-31', 'actual_start' => '2026-02-03', 'actual_end' => '2026-03-28', 'coordinator_id' => $quality->id]);
        $check = AmiChecklistItem::firstOrCreate(['ami_cycle_id' => $ami->id, 'code' => 'AMI-CHK-001'], ['instrument_node_id' => null, 'code' => 'AMI-CHK-001', 'question' => 'Standar pendidikan telah dipenuhi sesuai target.', 'response_type' => 'boolean', 'response_status' => 'answered', 'score' => 3, 'response' => 'Sebagian besar terpenuhi', 'auditor_notes' => 'Perlu penguatan pemerataan layanan.', 'evidence_required' => true, 'sort_order' => 1]);
        $finding = AmiFinding::firstOrCreate(['ami_cycle_id' => $ami->id, 'code' => 'AMI-FND-001'], ['ami_checklist_item_id' => $check->id, 'reported_by' => $quality->id, 'classification' => 'minor', 'severity' => 'medium', 'requirement' => 'Target kepuasan mahasiswa', 'condition' => 'Capaian 78% dari target 85%.', 'criteria' => 'Target SPMI 2026', 'cause' => 'Monitoring belum merata.', 'impact' => 'Perbaikan layanan belum konsisten.', 'recommendation' => 'Tetapkan monitoring bulanan.', 'status' => 'open']);
        $meeting = RtmMeeting::firstOrCreate(['code' => 'RTM-DEMO-2026'], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'ami_cycle_id' => $ami->id, 'title' => 'Rapat Tinjauan Manajemen Demo', 'held_at' => '2026-04-15 09:00:00', 'status' => 'completed', 'minutes' => 'Rapat menyepakati tindak lanjut temuan AMI.', 'chair_id' => $quality->id]);
        $decision = RtmDecision::firstOrCreate(['code' => 'RTM-DEC-001'], ['rtm_meeting_id' => $meeting->id, 'ami_finding_id' => $finding->id, 'code' => 'RTM-DEC-001', 'decision' => 'Melakukan monitoring kepuasan mahasiswa setiap bulan.', 'rationale' => 'Menutup temuan AMI.', 'status' => 'approved']);
        $rtl = RtlAction::firstOrCreate(['code' => 'RTL-DEMO-001'], ['perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'ami_finding_id' => $finding->id, 'rtm_decision_id' => $decision->id, 'owner_id' => $quality->id, 'title' => 'Monitoring bulanan kepuasan mahasiswa', 'action_plan' => 'Menyusun dashboard monitoring dan rapat evaluasi bulanan.', 'due_date' => '2026-10-30', 'progress_percent' => 65, 'status' => 'in_progress']);

        $node = InstrumentNode::firstOrCreate(['instrument_version_id' => $version->id, 'code' => 'NODE-DEMO-001'], ['node_type' => 'criterion', 'title' => 'Tata Pamong dan Mutu', 'requirement' => 'Dokumen dan proses mutu tersedia.', 'guidance' => 'Lengkapi evidence pendukung.', 'weight' => 20, 'sort_order' => 1, 'is_required' => true]);
        $section = AccreditationSection::firstOrCreate(['accreditation_id' => $accreditation->id, 'code' => 'LED-SEC-001'], ['instrument_node_id' => $node->id, 'title' => 'Tata Pamong dan Mutu', 'section_type' => 'led', 'sort_order' => 1, 'status' => 'draft', 'readiness_percent' => 75]);
        $response = AccreditationResponse::firstOrCreate(['accreditation_id' => $accreditation->id, 'response_key' => 'LED-SEC-001'], ['accreditation_section_id' => $section->id, 'instrument_node_id' => $node->id, 'response_type' => 'text', 'response_text' => 'Institusi menjalankan siklus PPEPP secara berkala.', 'status' => 'draft', 'last_edited_by' => $quality->id]);
        AccreditationReadinessItem::firstOrCreate(['accreditation_id' => $accreditation->id, 'item_key' => 'LED-SEC-001'], ['item_type' => 'section', 'status' => 'in_progress', 'notes' => 'Menunggu review evidence.', 'checked_at' => now(), 'checked_by' => $quality->id]);
        $run = ReadinessRun::firstOrCreate(['accreditation_id' => $accreditation->id, 'run_type' => 'full'], ['instrument_version_id' => $version->id, 'created_by' => $quality->id, 'status' => 'completed', 'engine_version' => 'v1-demo', 'total_items' => 1, 'ready_items' => 0, 'completion_percent' => 75, 'weighted_score' => 75, 'input_hash' => hash('sha256', 'readiness-demo'), 'summary' => ['catatan' => 'Masih terdapat gap evidence.'], 'started_at' => now()->subHour(), 'completed_at' => now()]);
        $result = ReadinessResult::firstOrCreate(['readiness_run_id' => $run->id, 'item_key' => 'NODE-DEMO-001'], ['instrument_node_id' => $node->id, 'assessment_element_id' => null, 'status' => 'partial', 'weight' => 20, 'completion_percent' => 75, 'evidence_percent' => 50, 'score' => 75, 'gap_count' => 1, 'details' => ['catatan' => 'Evidence belum lengkap.']]);
        ReadinessGap::firstOrCreate(['readiness_run_id' => $run->id, 'item_key' => 'NODE-DEMO-001'], ['readiness_result_id' => $result->id, 'gap_type' => 'evidence', 'severity' => 'medium', 'description' => 'Evidence pendukung belum lengkap.', 'resolution_status' => 'open']);

        $definition = DocumentDefinition::firstOrCreate(['code' => 'LAPORAN-MUTU-DEMO'], ['name' => 'Laporan Mutu Demo', 'domain' => 'reporting', 'scope_type' => 'program_studi', 'supported_formats' => ['pdf', 'docx', 'xlsx', 'html'], 'description' => 'Definisi output demo untuk pengujian engine.', 'is_active' => true]);
        foreach ([
            ['LAPORAN-SPMI-GENERIK', 'Ringkasan SPMI', 'spmi'],
            ['LAPORAN-AMI-GENERIK', 'Ringkasan AMI', 'ami'],
            ['LAPORAN-RTL-GENERIK', 'Rekap RTL dan RTM', 'rtl'],
            ['LAPORAN-EVIDENCE-GENERIK', 'Rekap Evidence', 'evidence'],
            ['LAPORAN-READINESS-GENERIK', 'Ringkasan Readiness', 'readiness'],
            ['LAPORAN-AKREDITASI-GENERIK', 'Ringkasan Akreditasi', 'akreditasi'],
        ] as [$code, $name, $domain]) {
            DocumentDefinition::firstOrCreate(['code' => $code], ['name' => $name, 'domain' => $domain, 'scope_type' => 'program_study', 'supported_formats' => ['html'], 'description' => 'Output generik tanpa template resmi.', 'is_active' => true]);
        }
        $template = DocumentTemplateVersion::firstOrCreate(['document_definition_id' => $definition->id, 'version_label' => '1.0', 'format' => 'html'], ['created_by' => $quality->id, 'schema' => ['sections' => ['ringkasan', 'evidence', 'rekomendasi']], 'template_hash' => hash('sha256', 'template-demo'), 'status' => 'published', 'published_by' => $quality->id, 'published_at' => now()]);
        $request = DocumentGenerationRequest::firstOrCreate(['document_definition_id' => $definition->id, 'period_label' => '2026'], ['document_template_version_id' => $template->id, 'perguruan_tinggi_id' => $pt->id, 'program_studi_id' => $prodi->id, 'requested_by' => $quality->id, 'parameters' => ['scenario' => 'sqm-full'], 'status' => 'completed', 'started_at' => now()->subMinutes(5), 'completed_at' => now()]);
        $payload = ['judul' => 'Laporan Mutu Demo 2026', 'scope' => 'Program Studi', 'readiness' => 75, 'catatan' => 'Data demo untuk pengujian.'];
        $snapshot = DocumentSnapshot::firstOrCreate(['document_generation_request_id' => $request->id], ['payload' => $payload, 'payload_hash' => hash('sha256', json_encode($payload)), 'source_context' => 'SPMI-AMI-AKREDITASI']);
        DocumentArtifact::firstOrCreate(['document_generation_request_id' => $request->id, 'format' => 'html'], ['document_snapshot_id' => $snapshot->id, 'file_name' => 'laporan-mutu-demo-2026.html', 'storage_provider' => 'local', 'storage_path' => 'document-output/demo/laporan-mutu-demo-2026.html', 'mime_type' => 'text/html', 'status' => 'draft']);
    }
}
