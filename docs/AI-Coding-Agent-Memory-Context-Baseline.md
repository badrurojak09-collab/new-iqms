# `new-qms` — AI Coding Agent Memory & Context Baseline

**Dokumen untuk:** Aider dan AI Coding Agent lain  
**Nama proyek:** `new-qms` / Smart Quality Management (SQM)  
**Tanggal baseline:** 28 Agustus 2026  
**Framework utama:** Laravel 13 dan Filament 5  
**Database utama:** MySQL 8.4  
**Bahasa UI:** Bahasa Indonesia  
**Status:** Fondasi fungsional telah dibangun; hardening, testing, observability, staging, dan production readiness masih berlanjut.

> Dokumen ini adalah konteks kerja utama. Sebelum mengubah source, AI Agent wajib membaca dokumen ini, memeriksa file aktual yang disebutkan, memahami tenant boundary dan policy, lalu membuat perubahan sekecil mungkin tanpa merusak workflow yang sudah berjalan.

---

## 1. Project Overview & Objectives

### 1.1 Identitas proyek

`new-qms` adalah platform **Smart Quality Management (SQM)** untuk mengintegrasikan Sistem Penjaminan Mutu Internal (SPMI), Audit Mutu Internal (AMI), Rapat Tinjauan Manajemen (RTM), Rencana Tindak Lanjut (RTL), Evidence Center, Instrument Registry, dan pengelolaan akreditasi institusi maupun program studi.

Aplikasi ini dirancang untuk organisasi berbentuk yayasan yang dapat menaungi satu atau lebih perguruan tinggi. Setiap perguruan tinggi dapat memiliki banyak program studi. Data operasional harus terisolasi sesuai ruang lingkup user, perguruan tinggi, dan program studi.

### 1.2 Tujuan bisnis

Tujuan utama aplikasi adalah menyediakan satu sumber data mutu yang menghubungkan siklus peningkatan mutu internal dengan kebutuhan akreditasi eksternal. SPMI menghasilkan standar, indikator, target, realisasi, evaluasi, dan program peningkatan. AMI menguji ketercapaian dan menemukan ketidaksesuaian. RTM menetapkan keputusan manajemen. RTL mengelola tindakan perbaikan. Evidence Center menyimpan rujukan bukti berbasis tautan cloud. Instrument Registry menyediakan konfigurasi instrumen yang berversi. Readiness dan akreditasi menggunakan seluruh data tersebut untuk menghitung kesiapan, menghasilkan gap, menyusun LED/LKPS, dan membuat laporan.

### 1.3 Skema bisnis utama

```text
Yayasan
  └── Perguruan Tinggi
        └── Program Studi
              ├── SPMI: Framework → Standar → Indikator → Target → Realisasi → Evaluasi
              ├── AMI: Siklus → Checklist → Temuan → Tindak lanjut
              ├── RTM: Rapat → Keputusan → RTL
              ├── RTL: Action → Evidence outcome → Effectiveness review → Verified
              └── Akreditasi: Instrumen → Mapping → Evidence → LED/LKPS → Readiness → Submission
```

### 1.4 Prinsip bisnis yang tidak boleh dilanggar

| Prinsip | Makna implementasi |
|---|---|
| Satu sumber kebenaran | Data mutu tidak boleh disalin secara tidak terkendali antar modul. Gunakan relasi dan mapping. |
| Tenant isolation | User hanya boleh melihat dan mengubah data dalam scope yang diberikan. |
| Versioned instrument | Instrumen, rubric, threshold, dan rule yang sudah dipakai tidak diedit langsung. Buat versi baru. |
| Evidence link-only | Sistem menyimpan tautan Google Drive/cloud serta metadata dan riwayat pemeriksaan; file fisik bukan disimpan di aplikasi. |
| Separation of duties | Reviewer, approver, operator, dan super admin memiliki tanggung jawab berbeda. |
| Immutable history | Score snapshot, audit event, dan versi instrumen harus dapat ditelusuri serta tidak berubah diam-diam. |
| Indonesian UI | Semua label, heading, action, validation message, status, dan keterangan UI harus berbahasa Indonesia. |

---

## 2. Tech Stack & Environment

### 2.1 Framework dan dependency inti

| Komponen | Nilai |
|---|---|
| Backend | Laravel 13 |
| Admin panel | Filament 5 |
| PHP | 8.3 |
| Database | MySQL 8.4 |
| Local development | Laragon, terutama pada Windows |
| Authorization | Spatie Permission ditambah policy Laravel eksplisit |
| Frontend admin | Filament/Livewire/Alpine sesuai dependency Filament 5 |
| Spreadsheet import | PhpSpreadsheet melalui `IOFactory` |
| Queue | Laravel Queue; digunakan oleh job re-evaluation readiness |
| Storage evidence | External cloud link; tidak menggunakan upload file fisik sebagai pola bisnis utama |
| Testing | PHPUnit/Laravel Feature dan Unit tests |

### 2.2 Struktur direktori aplikasi

Root aplikasi Laravel berada di:

```text
/home/ubuntu/sqm-platform-redesign/app
```

Struktur penting:

```text
app/
├── Console/Commands/
├── Domain/
├── Filament/
│   ├── Pages/
│   ├── Resources/
│   └── Widgets/
├── Http/
│   ├── Controllers/
│   └── Middleware/
├── Jobs/
├── Models/
├── Observers/
├── Policies/
├── Providers/
└── Support/
    ├── Audit/
    ├── Tenancy/
    └── Ui/

database/
├── migrations/
├── seeders/
└── factories/

resources/views/
routes/
tests/
```

### 2.3 Konfigurasi lokal

File `.env` lokal pengguna bersifat rahasia dan tidak boleh diubah oleh AI Agent tanpa permintaan eksplisit. Nilai minimal yang diperlukan:

```dotenv
APP_NAME=new-qms
APP_ENV=local
APP_KEY=...
APP_DEBUG=true
APP_URL=http://127.0.0.1

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=new_sqm_db
DB_USERNAME=...
DB_PASSWORD=...
```

Perintah pemeriksaan umum:

```bash
php artisan optimize:clear
php artisan migrate:status
php artisan route:list
php artisan test
```

Jangan menggunakan `migrate:fresh` pada database yang berisi data pengguna kecuali pengguna secara eksplisit meminta reset database lokal.

---

## 3. Architecture & Core Concepts

## 3.1 Multi-tenancy dan Tenant Context

### 3.1.1 Hirarki scope

SQM menggunakan tiga level organisasi utama:

```text
Yayasan        = scope tertinggi organisasi pemilik
PerguruanTinggi = tenant operasional yang berada di bawah Yayasan
ProgramStudi   = scope unit akademik di bawah Perguruan Tinggi
```

User tidak cukup hanya memiliki role. User juga harus memiliki satu atau lebih record `UserTenantScope`. Record tersebut menentukan kombinasi:

```text
user_id
scope_type
scope_id
is_default
metadata/status sesuai schema aktual
```

`scope_type` menentukan apakah akses berlaku pada yayasan, perguruan tinggi, atau program studi. Jangan mengasumsikan semua user memiliki satu PT atau satu prodi.

### 3.1.2 Komponen tenant utama

| Komponen | Lokasi | Tanggung jawab |
|---|---|---|
| `TenantContext` | `app/Support/Tenancy/TenantContext.php` | Menyimpan konteks tenant aktif selama request. |
| `ResolveTenantContext` | `app/Http/Middleware/ResolveTenantContext.php` | Menentukan dan memuat konteks tenant dari user/session/request. |
| `UserTenantScope` | `app/Models/UserTenantScope.php` | Menyimpan hubungan user dengan scope organisasi. |
| `HasTenantScope` | `app/Models/Concerns/HasTenantScope.php` | Concern untuk model yang membutuhkan tenant-aware filtering. |
| `ScopedRoleManager` | `app/Support/Tenancy/ScopedRoleManager.php` | Membantu pengelolaan role berdasarkan ruang lingkup. |
| Policy | `app/Policies/*Policy.php` | Otorisasi per model dan pemeriksaan tenant. |
| `UserTenantScopeObserver` | `app/Observers/UserTenantScopeObserver.php` | Audit/validasi perubahan scope user. |

### 3.1.3 Aturan query tenant

Setiap query terhadap data operasional wajib menjawab pertanyaan berikut:

1. Apakah model memiliki `perguruan_tinggi_id`, `program_studi_id`, `yayasan_id`, atau relasi ke model yang memiliki scope tersebut?
2. Apakah `TenantContext` sudah ter-resolve pada saat query dijalankan?
3. Apakah policy memeriksa scope user, bukan hanya role?
4. Apakah global search, relation manager, export, report, job, dan endpoint controller memakai filter tenant yang sama?
5. Apakah super admin aplikasi memang boleh bypass sesuai policy, atau hanya role tenant-level?

Pola aman:

```php
$query->whereIn('program_studi_id', $tenantContext->allowedProgramStudyIds());
```

atau gunakan scope/model helper yang sudah disediakan proyek. Jangan menambahkan `where('user_id', auth()->id())` sebagai pengganti tenant isolation karena satu user dapat memiliki banyak scope dan data dapat dimiliki unit, bukan user.

### 3.1.4 Super admin dan impersonation

`super_admin` adalah role aplikasi dengan akses lintas tenant dan akses administratif penuh sesuai policy. Fitur impersonation hanya boleh digunakan oleh super admin aplikasi. Impersonation menyimpan identitas user asli, identitas user yang diperankan, dan audit event. Session harus dipertahankan agar user tidak terlempar ke login.

Jangan memperluas impersonation ke role lain. Jangan menghapus middleware autentikasi/session hanya untuk membuat impersonation berjalan. Perubahan autentikasi harus diuji bersama policy, session preservation, logout, dan audit log.

---

## 3.2 Domain SPMI dan PPEPP

SPMI mengikuti siklus PPEPP:

```text
Penetapan → Pelaksanaan → Evaluasi → Pengendalian → Peningkatan
```

Entitas utama:

```text
SpmiFramework
  └── SpmiStandard
        └── SpmiIndicator
              └── SpmiTarget
                    └── SpmiRealization
                          └── SpmiEvaluation

SpmiImprovementProgram → re-evaluation readiness → PPEPP feedback loop
```

### 3.2.1 Alur SPMI

1. Operator atau penanggung jawab membuat framework SPMI untuk PT/prodi dan periode yang sesuai.
2. Standar mutu dimasukkan di bawah framework.
3. Setiap standar memiliki indikator terukur.
4. Setiap indikator memiliki target, periode, satuan, arah evaluasi, dan kebutuhan evidence.
5. Pelaksana mengisi realisasi.
6. Evaluator melakukan evaluasi dan menentukan status ketercapaian.
7. Jika terdapat gap, dibuat program peningkatan.
8. Program peningkatan bergerak dari `planned` ke `in_progress`, `submitted`, `verified`, atau status yang ditentukan service aktual.
9. Ketika program diverifikasi, job asynchronous melakukan re-evaluation readiness.
10. Feedback program masuk kembali ke siklus PPEPP.

### 3.2.2 Service SPMI aktif

```text
app/Domain/Spmi/EvaluateSpmiRealization.php
app/Domain/Spmi/VerifySpmiRealization.php
app/Domain/Quality/SpmiImprovementProgramLifecycleService.php
```

Aturan: perubahan status SPMI tidak boleh dilakukan dengan update database langsung jika transition service tersedia. Service menangani guard, audit, dan efek lintas domain.

---

## 3.3 Domain AMI, RTM, dan RTL

### 3.3.1 AMI

AMI menguji standar dan indikator melalui siklus audit:

```text
AmiCycle
  ├── AmiAssignment
  ├── AmiChecklistItem
  └── AmiFinding
```

Alur dasar:

```text
Buat siklus → tetapkan auditor → susun checklist → isi hasil audit
→ catat temuan → review → tindak lanjut → close/verify
```

Service dan observer:

```text
app/Domain/Ami/AmiCycleLifecycleService.php
app/Observers/AmiCycleObserver.php
app/Observers/AmiFindingObserver.php
```

`AmiFinding` harus memiliki konteks audit, standar/indikator yang relevan, deskripsi temuan, klasifikasi/status, rekomendasi, dan tautan ke RTL atau readiness gap bila relevan.

### 3.3.2 RTM

RTM merupakan forum keputusan manajemen terhadap hasil AMI, readiness, dan isu mutu. Entitas utama:

```text
RtmMeeting
  ├── RtmParticipant
  └── RtmDecision
```

RTM tidak boleh dipahami sebagai sekadar tabel catatan rapat. `RtmDecision` harus dapat menaut ke temuan AMI, readiness gap, dan/atau RTL sesuai schema dan policy.

Observer aktif:

```text
app/Observers/RtmMeetingObserver.php
app/Observers/RtmDecisionObserver.php
```

### 3.3.3 RTL

RTL mengelola tindakan perbaikan dan efektivitasnya:

```text
RtlAction
  └── RtlEffectivenessReview
        └── evidence links outcome
```

Lifecycle umum:

```text
draft → planned → in_progress → submitted → verified → closed
```

Status aktual harus mengikuti enum/transition yang ada pada source. Gunakan:

```text
app/Domain/Quality/RtlActionLifecycleService.php
app/Domain/Quality/RtlEffectivenessReviewService.php
```

RTL yang sudah diverifikasi dapat menutup `ReadinessGap` melalui `ReadinessGapResolutionService` dan memicu feedback ke PPEPP. Jangan menutup gap hanya dengan mengganti status manual tanpa audit event.

---

## 3.4 Instrument Registry dan penilaian fleksibel

SQM tidak menanamkan satu versi instrumen secara hard-coded. Struktur instrumen harus dapat menampung perubahan BAN-PT, LAM, dan instrumen internal SPMI/AMI dari waktu ke waktu.

### 3.4.1 Hirarki instrumen

```text
AccreditationBody
  └── InstrumentFamily
        └── InstrumentVersion
              ├── InstrumentNode
              ├── AssessmentCriterion
              ├── AssessmentElement
              ├── AssessmentIndicator
              ├── AssessmentScale
              │     └── AssessmentScaleOption
              ├── AssessmentRubric
              ├── AssessmentThreshold
              └── InstrumentScoringRule
```

`InstrumentVersion` memiliki status lifecycle dan immutable behavior. Versi yang sudah dipublikasikan atau sudah dipakai dalam penilaian tidak boleh diubah langsung.

### 3.4.2 Canonical Instrument Engine

Service utama:

```text
app/Domain/InstrumentRegistry/ImportCanonicalInstrument.php
app/Domain/InstrumentRegistry/ImportInstrumentVersion.php
app/Domain/InstrumentRegistry/ApproveAssessmentConfiguration.php
app/Domain/InstrumentRegistry/PublishInstrumentVersion.php
```

Importer mendukung spreadsheet melalui PhpSpreadsheet dan entity type:

```text
node
criterion
element
indicator
scale
scale_option
rubric
threshold
qualification_rule
```

Kolom minimum importer:

```text
entity_type
code
title
```

Relasi wajib:

```text
criterion → node_code
 element  → node_code + criterion_code
indicator → element_code
scale_option → scale_code
threshold → indicator_code atau element_code
qualification_rule → rule_type
```

Baris harus diurutkan dari parent ke child. Jangan mengubah urutan pemrosesan importer tanpa menambahkan mekanisme dependency resolution.

### 3.4.3 Rubric

`AssessmentRubric` pada schema aktual tidak memiliki kolom `code` dan tidak memiliki `assessment_element_id`. Field utama yang tersedia adalah:

```text
instrument_version_id
instrument_node_id
assessment_scale_option_id
min_score
max_score
label
description
evidence_expectation
status
approved_by
approved_at
approval_notes
```

Ini adalah batas penting bagi AI Agent. Jangan membuat query rubric berdasarkan `code` kecuali migration baru secara eksplisit menambahkan kolom tersebut. Hubungan rubric dengan elemen direpresentasikan melalui node instrumen yang sesuai.

### 3.4.4 Threshold

`AssessmentThreshold` menyimpan threshold numerik dan agregasi. Field penting:

```text
instrument_version_id
assessment_element_id
assessment_indicator_id
assessment_scale_id
assessment_rubric_id
code
name
comparison
target_value
min_value
max_value
pass_score
fail_score
minimum_score
weight
status
config
source_reference
direction
aggregation_key
aggregation_operator
aggregation_min_passed
sequence
approved_by
approved_at
approval_notes
```

Threshold dan rubric berbeda:

| Komponen | Kegunaan |
|---|---|
| Rubric | Menentukan label/deskripsi penilaian berdasarkan skor atau rentang. |
| Threshold | Menentukan lulus/gagal atau kelas nilai berdasarkan angka, rasio, persentase, atau agregasi. |
| Scoring rule | Menentukan ekspresi perhitungan dan qualification status. |

### 3.4.5 Runtime evaluator

Service:

```text
app/Domain/Accreditation/RuntimeScoringEngine.php
app/Domain/Accreditation/ReadinessScoringService.php
```

Runtime harus mendukung:

```text
direction-aware evaluation
multi-threshold aggregation
rubric range evaluation
rule_type=status_qualification
qualification gate Unggul
```

`status_qualification` dapat menggunakan nilai agregat seperti skor total, rata-rata kelompok kriteria, minimum elemen, kelengkapan evidence, atau kondisi gate lain yang didefinisikan pada configuration. Jangan menanamkan formula LAM secara hard-coded pada controller atau Resource.

---

## 3.5 Evidence Center

### 3.5.1 Prinsip evidence

Evidence SQM menggunakan pendekatan **link-only**. Dokumen fisik disimpan pada Google Drive atau cloud storage institusi. SQM menyimpan:

```text
URL/tautan cloud
judul/nama evidence
jenis evidence
pemilik/scope
periode
versi
metadata
review status
link check history
integritas/hash metadata jika tersedia
```

Model utama:

```text
Evidence
EvidenceCollection
EvidenceCollectionItem
EvidenceLink
EvidenceLinkCheck
EvidenceReview
EvidenceVersion
DocumentEvidenceReference
```

Service:

```text
app/Domain/Evidence/EvidenceCollectionService.php
app/Domain/Evidence/EvidenceCollectionApprovalService.php
app/Domain/Evidence/StoreEvidenceLink.php
app/Domain/Evidence/StoreEvidenceDocument.php
app/Domain/Integration/LinkEvidenceToRecord.php
```

`StoreEvidenceDocument` tidak boleh diartikan sebagai kewajiban upload file fisik. Pola bisnis yang disepakati adalah tautan cloud. Jangan menambahkan upload lokal sebagai default tanpa keputusan baru.

### 3.5.2 Review evidence

Evidence dapat melalui status review. Review evidence memengaruhi readiness/scoring hanya jika evaluator memang memerlukan evidence valid. Status link seperti tidak diperiksa, valid, rusak, tidak dapat diakses, atau ditolak harus dipetakan secara eksplisit oleh service/evaluator.

History check link bersifat read-only dan harus dipertahankan untuk audit.

### 3.5.3 Collection lock dan approval

Collection evidence dapat mengalami lifecycle:

```text
draft → submitted → under_review → approved/rejected → locked
```

Nama status aktual harus diambil dari model/service terbaru. Collection yang sudah di-lock tidak boleh diubah tanpa mekanisme unlock berizin dan audit.

---

## 3.6 Akreditasi, LED/LKPS, readiness, dan submission

### 3.6.1 Entitas akreditasi

```text
Accreditation
  ├── AccreditationCriterion
  ├── AccreditationSection
  ├── AccreditationResponse
  ├── AccreditationAssessment
  ├── AccreditationReadinessItem
  ├── ReadinessRun
  │     ├── ReadinessResult
  │     ├── ReadinessMappingResult
  │     └── ReadinessGap
  ├── AccreditationScoreSnapshot
  └── AccreditationSubmission
```

### 3.6.2 Alur akreditasi

```text
Buat kegiatan akreditasi
  → pilih scope PT/prodi dan instrument version
  → mapping SPMI/AMI ke elemen instrumen
  → kumpulkan evidence
  → isi response/LED/LKPS
  → jalankan validasi
  → jalankan readiness scoring
  → selesaikan readiness gap melalui RTM/RTL
  → re-evaluation
  → simpan score snapshot
  → bangun submission manifest/package
  → review dan approval
  → submit/export sesuai kebutuhan
```

Service utama:

```text
app/Domain/Accreditation/CalculateReadiness.php
app/Domain/Accreditation/ReadinessScoringService.php
app/Domain/Accreditation/RuntimeScoringEngine.php
app/Domain/Accreditation/LedLkpsValidator.php
app/Domain/Accreditation/ReadinessGapResolutionService.php
app/Domain/Accreditation/BuildSubmissionManifest.php
app/Domain/Accreditation/SubmitAccreditation.php
```

### 3.6.3 Mapping

`InstrumentMapping` menghubungkan indikator internal SPMI/AMI dengan target elemen/indikator instrumen eksternal. Mapping harus menyimpan sumber, target, bobot/koefisien, required flag, dan metadata sesuai schema.

Jangan menganggap kode indikator internal sama dengan kode elemen LAM. Gunakan mapping eksplisit. Mapping harus divalidasi agar tidak mengarah ke versi instrumen yang salah.

### 3.6.4 LED/LKPS

Template model:

```text
LedTemplate
LedTemplateSection
LkpsTemplate
LkpsTemplateColumn
```

LED berisi narasi dan section. LKPS berisi data terstruktur berbasis kolom. Tipe kolom dapat berupa numeric, percentage, ratio, integer, date, text, atau enum sesuai implementasi template.

`LedLkpsValidator` harus dijalankan sebelum submission. Validasi minimal:

```text
field required terisi
mapping valid
periode konsisten
satuan benar
evidence tersedia
angka dapat diproses
instrument version konsisten
```

### 3.6.5 Readiness dan snapshot

`ReadinessRun` adalah eksekusi penilaian pada kondisi tertentu. `ReadinessResult` menyimpan hasil runtime. `ReadinessGap` menyimpan kekurangan yang harus diperbaiki.

`AccreditationScoreSnapshot` bersifat permanen/immutable. Snapshot harus menyimpan instrument version, input yang dipakai, skor per butir, gate/qualification result, total, dan waktu perhitungan. Jangan menghitung ulang histori lama dengan konfigurasi instrumen terbaru.

---

## 3.7 Document Output Engine

Document Output Engine menyediakan fondasi output generik dan future official templates.

Model utama:

```text
DocumentDefinition
DocumentTemplateVersion
DocumentGenerationRequest
DocumentSnapshot
DocumentArtifact
DocumentApproval
DocumentEvidenceReference
```

Service:

```text
app/Domain/DocumentOutput/DocumentOutputService.php
app/Domain/DocumentOutput/GenericReportService.php
app/Domain/DocumentOutput/Contracts/DocumentRenderer.php
```

Controller endpoint:

```text
app/Http/Controllers/DocumentOutputController.php
routes/web.php
```

Output generik yang sudah dipersiapkan mencakup laporan untuk SPMI, AMI, RTL, Evidence, dan Akreditasi. Resource riwayat request berada pada:

```text
app/Filament/Resources/DocumentGenerationRequests/
```

Endpoint preview dan download harus tetap melewati authentication dan policy. Artifact tidak boleh diakses hanya dengan mengetahui ID request.

Alur:

```text
DocumentDefinition
  → DocumentGenerationRequest
  → snapshot data
  → render artifact
  → completed/failed
  → preview HTML atau download
```

Official template LED/LKPS/PDF/Word/Excel merupakan tahap lanjutan. Jangan mengubah generic renderer menjadi renderer resmi tanpa spesifikasi template klien.

---

## 4. Implemented Features & History — From Zero to Current Baseline

### 4.1 Baseline awal

Proyek dimulai dari kebutuhan untuk menyatukan sistem lama dengan rancangan ERD baru untuk Yayasan, Perguruan Tinggi, Program Studi, SPMI, AMI, dan akreditasi institusi/prodi. Prinsip desain kemudian diarahkan ke Laravel 13, Filament 5, MySQL, multi-tenant, versioned instrument, evidence cloud link, dan UI Bahasa Indonesia.

### 4.2 P0 — Audit baseline dan arsitektur

Hasil utama:

- Audit struktur sistem lama dan rancangan target.
- Penyusunan alur domain SPMI, AMI, akreditasi, LED, dan LKPS.
- Penetapan model multi-tenant Yayasan → PT → Prodi.
- Penetapan policy tenant-aware.
- Penetapan versioning instrumen dan immutable history.

### 4.3 P1 — Canonical Instrument Engine

Fitur yang sudah dibangun:

- Accreditation body, instrument family, dan instrument version.
- Hierarki instrument node.
- Assessment criterion, element, indicator.
- Assessment scale dan scale option.
- Assessment rubric.
- Assessment threshold.
- Instrument scoring rule dan dependency foundation.
- Import canonical Excel/CSV/JSON foundation.
- Approval dan publish foundation.
- Immutable instrument version trait/behavior.

Resource utama:

```text
AccreditationBodies
InstrumentFamilies
InstrumentVersions
InstrumentNodes
AssessmentCriteria
AssessmentElements
AssessmentIndicators
AssessmentScales
AssessmentRubrics
AssessmentThresholds
InstrumentScoringRules
```

### 4.4 P2 — Mapping AMI/SPMI ke BAN-PT/LAM

Fitur:

- Instrument mapping.
- Mapping source indicator/standard internal ke target instrument node/element/indicator.
- Validasi mapping dan coverage foundation.
- Hubungan hasil readiness dengan mapping.

### 4.5 P3 — Evidence Collection dan cloud link

Fitur:

- Evidence collection.
- Evidence item/reference.
- Evidence cloud link.
- Evidence version/review.
- Link check history read-only.
- Attach existing evidence.
- Collection approval dan lock foundation.
- Integrity metadata/hash sesuai implementasi.

### 4.6 P4 — Readiness Scoring Engine

Fitur:

- Readiness run.
- Readiness result.
- Readiness gap.
- Evaluasi berdasarkan AssessmentElement dan InstrumentMapping.
- Direction-aware evaluation.
- Multi-threshold aggregation.
- Rubric range evaluation.
- Runtime `status_qualification`.
- Coverage dan scoring result.
- Automatic re-evaluation setelah improvement program terverifikasi.

### 4.7 P5 — Approval dan Quality Governance

Fitur:

- Approval konfigurasi threshold/rubric.
- Role instrument reviewer dan instrument approver.
- Policy eksplisit untuk resource.
- Audit log.
- Approval evidence collection.
- Approval/lock foundation.
- Score snapshot immutable.

### 4.8 P6 — SPMI, AMI, RTM, RTL, effectiveness, dan PPEPP

Fitur:

- Framework SPMI.
- Standard, indicator, target, realization, evaluation.
- Improvement program lifecycle.
- Async readiness re-evaluation job.
- AMI cycle lifecycle.
- Assignment/checklist/finding AMI.
- RTM meeting, participant, decision.
- RTL action lifecycle dan authorization guard.
- Readiness gap resolution melalui RTL verified.
- RTL effectiveness review.
- Evidence links untuk outcome review.
- PPEPP feedback loop.
- Dashboard PPEPP dan program improvement verified.

### 4.9 P7 — Import, migrasi, dan dual-run

Fondasi yang sudah ada:

```text
app/Console/Commands/LegacyMigrationImport.php
MigrationLedger.php
MigrationRun.php
MigrationException.php
```

Yang masih perlu diperkuat:

- Import preview yang lengkap.
- Reconciliation report.
- Rollback per batch.
- Mapping data lama ke canonical entities.
- Dual-run comparison.
- Idempotent import berbasis source hash.
- Error quarantine dan retry.

### 4.10 P8 — Security dan tenant hardening

Yang sudah tersedia:

- Tenant context.
- Tenant-aware search foundation.
- Explicit policies.
- RBAC dengan Spatie Permission.
- Audit logger.
- Super admin impersonation dengan session preservation.
- Audit event impersonation.
- Policy document output.

Yang perlu ditingkatkan:

- Audit semua query relation manager.
- Audit semua export/report/job.
- Pengujian cross-tenant negatif pada setiap resource.
- Rate limiting endpoint output.
- Secure headers dan production session/cookie policy.

### 4.11 P9–P12 — Status produksi

| Fase | Status |
|---|---|
| P9 Testing dan UAT | Belum selesai sepenuhnya; sebagian feature/unit tests sudah ada. |
| P10 Performance dan observability | Belum selesai. |
| P11 Backup, disaster recovery, staging | Belum selesai. |
| P12 Pilot dan production go-live | Belum dimulai. |

---

## 5. Resource Filament yang Sudah Tersedia

### 5.1 Organisasi dan administrasi

```text
Yayasans/YayasanResource.php
PerguruanTinggis/PerguruanTinggiResource.php
ProgramStudis/ProgramStudiResource.php
UserTenantScopes/UserTenantScopeResource.php
Users/UserResource.php
Roles/RoleResource.php
AuditLogs/AuditLogResource.php
```

### 5.2 SPMI

```text
SpmiFrameworks/SpmiFrameworkResource.php
SpmiStandards/SpmiStandardResource.php
SpmiIndicators/SpmiIndicatorResource.php
SpmiTargets/SpmiTargetResource.php
SpmiRealizations/SpmiRealizationResource.php
SpmiEvaluations/SpmiEvaluationResource.php
SpmiImprovementPrograms/SpmiImprovementProgramResource.php
```

### 5.3 AMI, RTM, RTL

```text
AmiCycles/AmiCycleResource.php
AmiChecklistItems/AmiChecklistItemResource.php
AmiFindings/AmiFindingResource.php
RtmMeetings/RtmMeetingResource.php
RtlActions/RtlActionResource.php
```

Relation manager dan domain model dapat menyediakan assignment, participants, decisions, checklist, temuan, dan effectiveness review.

### 5.4 Evidence

```text
Evidences/EvidenceResource.php
EvidenceCollections/EvidenceCollectionResource.php
```

### 5.5 Instrument Registry

```text
AccreditationBodies/AccreditationBodyResource.php
InstrumentFamilies/InstrumentFamilyResource.php
InstrumentVersions/InstrumentVersionResource.php
InstrumentNodes/InstrumentNodeResource.php
AssessmentCriteria/AssessmentCriterionResource.php
AssessmentElements/AssessmentElementResource.php
AssessmentIndicators/AssessmentIndicatorResource.php
AssessmentScales/AssessmentScaleResource.php
AssessmentRubrics/AssessmentRubricResource.php
AssessmentThresholds/AssessmentThresholdResource.php
InstrumentMappings/InstrumentMappingResource.php
InstrumentScoringRules/InstrumentScoringRuleResource.php
```

### 5.6 Akreditasi

```text
Accreditations/AccreditationResource.php
AccreditationCriteria/AccreditationCriterionResource.php
AccreditationAssessments/AccreditationAssessmentResource.php
AccreditationResponses/AccreditationResponseResource.php
ReadinessRuns/ReadinessRunResource.php
AccreditationScoreSnapshots/AccreditationScoreSnapshotResource.php
AccreditationSubmissions/AccreditationSubmissionResource.php
LedTemplates/LedTemplateResource.php
LkpsTemplates/LkpsTemplateResource.php
```

### 5.7 Reporting dan document output

```text
DocumentDefinitions/DocumentDefinitionResource.php
DocumentGenerationRequests/DocumentGenerationRequestResource.php
```

### 5.8 UI/UX Filament

Kesepakatan UI:

- Group menu utama memiliki icon.
- Submenu tidak perlu icon individual.
- Sidebar mengikuti workflow SQM: Dashboard, administrasi/super admin, organisasi, SPMI, AMI, RTM/RTL, Evidence, Instrument Registry, Akreditasi, Reporting.
- Form menggunakan card/section layout yang rapi, tidak padat, dan konsisten.
- Label, heading, helper text, status, action, dan validation message menggunakan Bahasa Indonesia.
- Relation dropdown menampilkan kode dan nama, bukan ID mentah saja.
- Kolom sortable harus menggunakan nama kolom/alias SQL yang valid untuk MySQL, bukan camelCase alias yang menghasilkan SQL invalid.

---

## 6. Model Utama

### 6.1 Organisasi

```text
Yayasan
PerguruanTinggi
ProgramStudi
User
UserTenantScope
```

### 6.2 Instrument dan assessment

```text
AccreditationBody
InstrumentFamily
InstrumentVersion
InstrumentNode
AssessmentCriterion
AssessmentElement
AssessmentIndicator
AssessmentScale
AssessmentScaleOption
AssessmentRubric
AssessmentThreshold
InstrumentScoringRule
InstrumentMapping
InstrumentImportBatch
InstrumentImportRow
```

### 6.3 Mutu internal

```text
SpmiFramework
SpmiStandard
SpmiIndicator
SpmiTarget
SpmiRealization
SpmiEvaluation
SpmiImprovementProgram
AmiCycle
AmiAssignment
AmiChecklistItem
AmiFinding
RtmMeeting
RtmParticipant
RtmDecision
RtlAction
RtlEffectivenessReview
```

### 6.4 Evidence dan akreditasi

```text
Evidence
EvidenceCollection
EvidenceCollectionItem
EvidenceLink
EvidenceLinkCheck
EvidenceReview
EvidenceVersion
Accreditation
AccreditationCriterion
AccreditationSection
AccreditationResponse
AccreditationAssessment
AccreditationReadinessItem
ReadinessRun
ReadinessResult
ReadinessMappingResult
ReadinessGap
AccreditationScoreSnapshot
AccreditationSubmission
```

### 6.5 Document output dan audit

```text
Document
DocumentApproval
DocumentArtifact
DocumentDefinition
DocumentEvidenceReference
DocumentGenerationRequest
DocumentSnapshot
DocumentTemplateVersion
AuditLog
```

---

## 7. Domain Service, Action, Job, Observer, dan Policy

### 7.1 Service/action penting

```text
Accreditation/BuildSubmissionManifest.php
Accreditation/CalculateReadiness.php
Accreditation/LedLkpsValidator.php
Accreditation/ReadinessGapResolutionService.php
Accreditation/ReadinessScoringService.php
Accreditation/RuntimeScoringEngine.php
Accreditation/SubmitAccreditation.php
Ami/AmiCycleLifecycleService.php
DocumentOutput/DocumentOutputService.php
DocumentOutput/GenericReportService.php
Evidence/EvidenceCollectionApprovalService.php
Evidence/EvidenceCollectionService.php
Evidence/StoreEvidenceDocument.php
Evidence/StoreEvidenceLink.php
InstrumentRegistry/ApproveAssessmentConfiguration.php
InstrumentRegistry/ImportCanonicalInstrument.php
InstrumentRegistry/ImportInstrumentVersion.php
InstrumentRegistry/PublishInstrumentVersion.php
Integration/LinkEvidenceToRecord.php
Quality/RtlActionLifecycleService.php
Quality/RtlEffectivenessReviewService.php
Quality/SpmiImprovementProgramLifecycleService.php
Reporting/AccreditationReportData.php
Reporting/QualityDashboardMetrics.php
Spmi/EvaluateSpmiRealization.php
Spmi/VerifySpmiRealization.php
Workflow/WorkflowTransition.php
```

### 7.2 Observers

```text
AmiCycleObserver
AmiFindingObserver
RtmDecisionObserver
RtmMeetingObserver
UserTenantScopeObserver
```

Observer digunakan untuk audit, default status, validasi perubahan, dan efek domain yang sesuai. Jangan memindahkan seluruh business logic ke observer jika service lifecycle sudah tersedia.

### 7.3 Policies

Sebagian besar resource memiliki policy eksplisit. Policy penting mencakup:

```text
AccreditationBodyPolicy
AccreditationCriterionPolicy
AccreditationPolicy
AccreditationScoreSnapshotPolicy
AccreditationSubmissionPolicy
AmiChecklistItemPolicy
AmiCyclePolicy
AmiFindingPolicy
AssessmentCriterionPolicy
AssessmentElementPolicy
AssessmentIndicatorPolicy
AssessmentRubricPolicy
AssessmentScalePolicy
AssessmentThresholdPolicy
AuditLogPolicy
DocumentGenerationRequestPolicy
EvidenceCollectionPolicy
EvidencePolicy
InstrumentFamilyPolicy
InstrumentMappingPolicy
InstrumentNodePolicy
InstrumentScoringRulePolicy
InstrumentVersionPolicy
LedTemplatePolicy
LkpsTemplatePolicy
PerguruanTinggiPolicy
ProgramStudiPolicy
ReadinessRunPolicy
RolePolicy
RtlActionPolicy
RtmDecisionPolicy
RtmMeetingPolicy
Spmi*Policy
UserPolicy
YayasanPolicy
```

Semua perubahan policy harus mempertimbangkan:

```text
role permission
user tenant scope
resource ownership
workflow status
separation of duties
super_admin exception
```

---

## 8. Roles dan Tanggung Jawab

Role yang pernah digunakan dalam desain/UAT:

| Role | Fokus utama |
|---|---|
| `super_admin` | Administrasi aplikasi lintas tenant, impersonation, semua modul, konfigurasi global. |
| `security_admin` | RBAC, user, role, permission, audit, pemeriksaan keamanan. |
| `yayasan_admin` | Mengelola yayasan dan melihat/kelola perguruan tinggi sesuai scope yayasan. |
| `pt_admin` | Mengelola data operasional perguruan tinggi dan program studi sesuai scope. |
| `quality_manager` | Mengelola framework mutu, konfigurasi mutu, readiness, quality governance. |
| `lpm` / pengelola mutu | Mengelola SPMI, AMI, RTM, RTL, evidence, readiness. Nama role harus mengikuti seeder aktual. |
| `ami_auditor` | Melaksanakan checklist audit dan mencatat temuan. |
| `ami_reviewer` | Mereview hasil audit/temuan. |
| `spmi_operator` | Mengisi indikator, target, realisasi, dan data mutu. |
| `kaprodi` | Memvalidasi data program studi, evidence, response, dan RTL pada scope prodi. |
| `instrument_reviewer` | Meninjau struktur, rubric, threshold, mapping, dan import instrumen. |
| `instrument_approver` | Menyetujui konfigurasi instrumen sebelum publish. |

Nama role dan permission final harus selalu dibaca dari `RolePermissionSeeder.php`, bukan diasumsikan dari dokumen ini.

---

## 9. Development Conventions & Strict Rules

## 9.1 Aturan umum PHP/Laravel

1. Gunakan `declare(strict_types=1);` pada file PHP baru.
2. Ikuti namespace dan struktur folder yang sudah ada.
3. Gunakan type declaration untuk parameter dan return type.
4. Gunakan Eloquent relationship yang sudah ada daripada query manual berulang.
5. Gunakan service/action untuk lifecycle dan business rule penting.
6. Gunakan `DB::transaction()` untuk operasi multi-record yang harus atomik.
7. Gunakan `firstOrCreate`/`updateOrCreate` dengan kunci bisnis yang benar-benar ada di schema.
8. Jangan menggunakan field yang tidak ada pada migration.
9. Jangan menjalankan migration destructive pada data existing tanpa persetujuan.
10. Nama kolom database tetap berbahasa Inggris sesuai source; Bahasa Indonesia berlaku untuk UI.

## 9.2 Filament 5

- Periksa syntax Filament 5 yang dipakai proyek sebelum menyalin pola dari Filament 3/4.
- Form diletakkan pada `Schemas/*Form.php` jika Resource mengikuti pola tersebut.
- Table diletakkan pada `Tables/*Table.php`.
- Relation manager diletakkan pada `RelationManagers/*`.
- Gunakan `Filament\Forms\Components` dan `Filament\Tables\Columns` sesuai versi terpasang.
- Jangan memakai method yang tidak tersedia pada class/versi aktual. Contoh historis: `TextColumn::boolean()` pernah menimbulkan error; gunakan API yang benar untuk versi yang terpasang.
- Dropdown relasi wajib menampilkan informasi bisnis yang membantu, misalnya kode dan nama.
- Semua form menggunakan card/section layout konsisten.
- Jangan membagi form menjadi banyak kolom sempit tanpa alasan UX.
- Semua label UI harus Bahasa Indonesia.

## 9.3 Database dan MySQL

- MySQL membatasi panjang identifier index/foreign key. Beri nama index dan constraint secara eksplisit jika nama otomatis terlalu panjang.
- Jangan memakai alias camelCase pada `counts()` atau sortable query yang diterjemahkan menjadi identifier SQL invalid.
- Jangan membuat foreign key otomatis dengan nama panjang untuk tabel/kolom panjang.
- Periksa migration status sebelum membuat migration baru.
- Migration harus reversible jika memungkinkan.
- Data historis dan versi immutable tidak boleh dihapus oleh seeder biasa.

## 9.4 Seeder dan import

Seeder harus:

```text
idempotent
transactional
version-aware
source-referenced
safe for re-run
non-destructive terhadap versi aktif
```

Jika schema tidak memiliki `code` pada `assessment_rubrics`, jangan menggunakan `code` di `where` seeder. Kunci yang sesuai harus mengikuti schema aktual, misalnya kombinasi version, instrument node, dan scale option.

Seeder LAM INFOKOM yang tersedia:

```text
database/seeders/LamInfokom21CriteriaSeeder.php
```

Seeder tersebut adalah baseline draft. Descriptor, bobot, dan threshold rinci harus direkonsiliasi dengan dokumen resmi sebelum publish.

## 9.5 Evidence

- Jangan mengubah pola link-only menjadi upload fisik default.
- Jangan menaruh token/private URL di log.
- Sanitasi dan validasi URL.
- Pertahankan link check history.
- Jangan menghapus evidence yang sudah menjadi referensi snapshot/submission tanpa kebijakan retention.

## 9.6 Scoring dan immutable history

- Jangan menanamkan rule LAM/BAN-PT di controller.
- Jangan mengubah snapshot lama.
- Jangan mengubah instrument version aktif.
- Setiap perubahan rubric/threshold harus melalui draft → review → approval → publish.
- Setiap runtime result harus menyimpan referensi versi instrumen.
- Perubahan evaluator harus disertai test untuk higher/lower-is-better, threshold boundary, rubric range, aggregation, dan qualification.

## 9.7 Security

- Policy bukan opsional.
- Jangan memperluas bypass `super_admin` ke role lain.
- Impersonation hanya untuk `super_admin`.
- Semua endpoint output wajib authorization.
- Semua query global search wajib tenant-aware.
- Jangan mematikan middleware autentikasi untuk menyelesaikan masalah session tanpa audit dampak.
- Jangan mengungkap data tenant lain melalui dropdown, count, export, report, error message, atau relation manager.

---

## 10. Hal yang Tidak Boleh Diubah Sembarangan oleh Aider

Aider atau AI Agent **tidak boleh** melakukan perubahan berikut tanpa persetujuan eksplisit dan pemeriksaan dampak:

1. Mengganti Laravel atau Filament major version.
2. Mengubah struktur hirarki Yayasan → Perguruan Tinggi → Program Studi.
3. Menghapus atau melewati `TenantContext` dan `ResolveTenantContext`.
4. Menghapus policy atau mengganti semua policy dengan pemeriksaan role sederhana.
5. Memperluas fitur impersonation selain super admin.
6. Mengubah evidence menjadi upload fisik sebagai pola default.
7. Menghapus immutable versioning atau score snapshot.
8. Mengedit versi instrumen yang sudah aktif.
9. Mengubah formula scoring tanpa test regresi dan approval.
10. Menghapus audit log, link history, approval history, atau source hash.
11. Menggunakan ID mentah sebagai satu-satunya label dropdown bisnis.
12. Mengubah status lifecycle tanpa melalui transition service.
13. Menambahkan foreign key/index tanpa nama pendek yang aman untuk MySQL.
14. Menjalankan `migrate:fresh`, `db:wipe`, atau penghapusan massal data tanpa izin.
15. Mengubah semua label UI ke bahasa Inggris.
16. Menambahkan integrasi API BAN-PT/LAM/email/WhatsApp tanpa keputusan baru. Integrasi eksternal pernah disepakati untuk ditunda.
17. Menganggap tiga dokumen LAM INFOKOM sebagai data final tanpa rekonsiliasi source PDF.
18. Mengklaim test berhasil jika hanya lint atau tanpa menjalankan test yang relevan.

---

## 11. Testing Baseline

Test yang sudah tersedia di source antara lain:

```text
tests/Feature/AccreditationAggregateTest.php
tests/Feature/AccreditationDeadlineNotificationTest.php
tests/Feature/AmiRtmRtlTest.php
tests/Feature/CrossDomainIntegrationTest.php
tests/Feature/EvidenceStorageTest.php
tests/Feature/ImpersonationTest.php
tests/Feature/InstrumentVersionTest.php
tests/Feature/PermissionMappingTest.php
tests/Feature/SortableColumnAuditTest.php
tests/Feature/SpmiPpeppTest.php
tests/Feature/TenantAwareGlobalSearchTest.php
tests/Feature/TenantIsolationTest.php
tests/Feature/UserTenantScopeResourceTest.php
tests/Feature/YayasanResourceTest.php
tests/Unit/CanonicalImportV2Test.php
tests/Unit/IndonesianUiTest.php
tests/Unit/ReevaluateAccreditationReadinessJobTest.php
tests/Unit/StatusQualificationEvaluatorTest.php
```

Sebelum dan sesudah perubahan:

```bash
php artisan optimize:clear
php artisan test
```

Perubahan yang menyentuh tenant wajib menambah atau menjalankan test:

```text
akses tenant benar
cross-tenant read ditolak
cross-tenant update ditolak
relation manager terisolasi
global search terisolasi
report/export terisolasi
job terisolasi
```

Perubahan scoring wajib menguji:

```text
nilai minimum
nilai maksimum
nilai tepat di boundary
higher_is_better
lower_is_better
multi-threshold
rubric range
status_qualification
evidence tidak valid
gap resolved dan re-evaluation
snapshot tetap
```

---

## 12. Next Roadmap / Future Tasks

### 12.1 Prioritas P0 — stabilisasi pra-produksi

1. Jalankan regression test seluruh modul setelah setiap perubahan besar.
2. Selesaikan UAT per role dan tenant.
3. Audit tenant isolation untuk semua Resource, relation manager, service, job, export, report, dan global search.
4. Lengkapi validasi policy pada document output, readiness, evidence, dan submission.

### 12.2 Prioritas P1 — LAM INFOKOM production configuration

1. Rekonsiliasi penuh tiga dokumen LAM INFOKOM terhadap source asli.
2. Lengkapi semua kriteria, subkriteria, elemen, indikator, bobot, dan source page.
3. Lengkapi descriptor skor per butir.
4. Lengkapi threshold bertingkat rasio/persentase/nilai.
5. Lengkapi rule Unggul dan status qualification.
6. Tambahkan test angka resmi dan boundary.
7. Review oleh instrument reviewer.
8. Approval oleh instrument approver.
9. Publish sebagai immutable active version.

### 12.3 Prioritas P2 — Import dan migrasi

1. Buat UI import tiga tahap: upload, preview, commit.
2. Tampilkan error per baris.
3. Simpan source hash dan batch.
4. Tambahkan rollback per batch.
5. Buat reconciliation report.
6. Selesaikan legacy migration mapping.
7. Implementasikan dual-run comparison.

### 12.4 Prioritas P3 — Official document templates

1. Kumpulkan template resmi LED.
2. Kumpulkan template resmi LKPS.
3. Definisikan placeholder, field mapping, format, dan aturan validasi.
4. Implementasikan renderer HTML/PDF/Word/Excel sesuai prioritas klien.
5. Tambahkan approval dan artifact versioning.

### 12.5 Prioritas P4 — Operasional dan observability

1. Konfigurasi queue worker production.
2. Konfigurasi scheduler untuk pengingat dan re-evaluation.
3. Tambahkan failed job monitoring.
4. Tambahkan structured logging dan correlation ID.
5. Tambahkan performance baseline query.
6. Audit N+1 query di table dan relation manager.
7. Tambahkan cache hanya pada data yang aman dan version-aware.

### 12.6 Prioritas P5 — Backup, disaster recovery, staging, dan go-live

1. Definisikan backup database, object/link metadata, dan konfigurasi.
2. Lakukan restore drill.
3. Buat staging environment.
4. Jalankan UAT sign-off.
5. Jalankan security testing.
6. Jalankan load/performance testing.
7. Pilot pada satu PT/prodi.
8. Buat rollback plan.
9. Lakukan production go-live bertahap.
10. Monitor incident dan kualitas data setelah go-live.

---

## 13. Checklist AI Agent Sebelum Mengubah Source

```text
[ ] Sudah membaca dokumen baseline ini.
[ ] Sudah menemukan file target aktual.
[ ] Sudah memeriksa migration/schema kolom yang akan digunakan.
[ ] Sudah memeriksa model fillable/casts/relasi.
[ ] Sudah memeriksa policy dan tenant boundary.
[ ] Sudah memeriksa apakah service lifecycle tersedia.
[ ] Sudah memastikan perubahan tidak bypass TenantContext.
[ ] Sudah memastikan semua UI baru berbahasa Indonesia.
[ ] Sudah memastikan form memakai layout card yang konsisten.
[ ] Sudah mempertimbangkan MySQL identifier length.
[ ] Sudah menambahkan atau memperbarui test relevan.
[ ] Sudah menjalankan lint/test yang relevan.
[ ] Sudah menjelaskan file yang berubah dan risiko migrasi.
[ ] Tidak mengubah versi instrumen aktif atau snapshot lama.
```

## 14. Format Laporan Perubahan untuk Aider

Setiap pekerjaan sebaiknya diakhiri dengan format berikut:

```markdown
## Perubahan
- File:
- Tujuan:
- Perilaku baru:

## Tenant dan security impact
- Scope yang terpengaruh:
- Policy yang diperiksa:
- Risiko cross-tenant:

## Database impact
- Migration baru:
- Kolom/index/foreign key:
- Dampak data existing:

## Testing
- Command:
- Hasil:
- Skenario negatif:

## Catatan
- Hal yang belum final:
- Manual verification yang diperlukan:
- Rekomendasi langkah berikutnya:
```

---

## 15. Referensi Source dan Dokumen Internal

Dokumen ini bersandar pada source proyek dan artefak internal berikut:

1. `README.md`
2. `SQM_IMPLEMENTATION_MANIFEST.md`
3. `app/Support/Tenancy/TenantContext.php`
4. `app/Http/Middleware/ResolveTenantContext.php`
5. `app/Support/Tenancy/ScopedRoleManager.php`
6. `app/Domain/InstrumentRegistry/ImportCanonicalInstrument.php`
7. `app/Domain/Accreditation/RuntimeScoringEngine.php`
8. `app/Domain/Accreditation/ReadinessScoringService.php`
9. `app/Domain/Evidence/EvidenceCollectionService.php`
10. `app/Domain/DocumentOutput/DocumentOutputService.php`
11. `database/seeders/RolePermissionSeeder.php`
12. `database/seeders/SqmScenarioSeeder.php`
13. `database/seeders/LamInfokom21CriteriaSeeder.php`
14. `tests/Feature/TenantIsolationTest.php`
15. `tests/Feature/ImpersonationTest.php`
16. `tests/Unit/StatusQualificationEvaluatorTest.php`
17. `sqm-pdf-analysis/ANALISIS_TIGA_DOKUMEN_INSTRUMEN_SQM.md`

> Referensi di atas adalah path repository/workspace, bukan klaim bahwa dokumen eksternal sudah final atau seluruh konfigurasi regulasi sudah tervalidasi. Untuk konfigurasi akreditasi production, source resmi dan dokumen client tetap menjadi sumber kebenaran.

---

## 16. Ringkasan Satu Halaman untuk Agent

`new-qms` adalah aplikasi Laravel 13 + Filament 5 untuk SQM multi-tenant. Hirarki tenant adalah Yayasan → Perguruan Tinggi → Program Studi. Tenant context ditentukan oleh `UserTenantScope`, diproses oleh `ResolveTenantContext`, disimpan pada `TenantContext`, dan ditegakkan oleh policy/query/service. SPMI memakai PPEPP. AMI menghasilkan checklist dan temuan. RTM menghasilkan keputusan. RTL menutup gap dan memberi feedback ke PPEPP. Evidence hanya berupa tautan cloud. Instrument Registry berversi dan immutable. Mapping menghubungkan indikator internal dengan elemen eksternal. Runtime scoring mendukung threshold, rubric, direction, aggregation, dan `status_qualification`. Readiness menghasilkan gap; RTL verified dapat menyelesaikannya; snapshot skor tidak boleh berubah. Document Output Engine menghasilkan laporan generik dan menyimpan request/snapshot/artifact dengan endpoint yang diproteksi policy.

Semua UI harus berbahasa Indonesia, berbentuk card yang konsisten, dan menampilkan label bisnis bukan ID. Semua Resource wajib punya policy. Jangan mengedit versi instrumen aktif, snapshot, audit history, atau data tenant lain. Jangan mengubah evidence menjadi upload fisik. Jangan menambah API eksternal tanpa keputusan baru. Selalu baca migration/model/policy/service sebelum coding dan jalankan test setelah perubahan.
