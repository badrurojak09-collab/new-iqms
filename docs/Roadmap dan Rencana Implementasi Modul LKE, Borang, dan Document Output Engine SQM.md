# Roadmap dan Rencana Implementasi Modul LKE, Borang, dan Document Output Engine SQM

## 1. Tujuan

Roadmap ini merumuskan pengembangan modul **LKE/self-assessment**, **LED**, **Borang/LKPS**, serta **Document Output Engine** yang terintegrasi dengan SPMI, AMI, RTM, RTL, Evidence Center, Readiness Scoring, dan Submission Package.

Roadmap disusun berdasarkan dua sumber utama: kebutuhan dokumen luaran pada lampiran pengguna dan kondisi aktual repository `new-iqms`. Prinsip utamanya adalah memperluas engine yang sudah ada, bukan membuat modul terpisah untuk setiap LAM atau jenis instrumen.

> Satu versi instrumen harus dapat menghasilkan beberapa jenis pekerjaan dan dokumen: self-assessment/LKE, LED naratif, borang/LKPS kuantitatif, matriks evidence, simulasi skor, dan submission package.

## 2. Prinsip Arsitektur

### 2.1 Instrumen tetap generik dan immutable

`InstrumentFamily`, `InstrumentVersion`, `InstrumentNode`, `AssessmentElement`, `AssessmentRubric`, `AssessmentThreshold`, dan `InstrumentMapping` tetap menjadi sumber konfigurasi. Setiap pelaksanaan harus menunjuk ke `instrument_version_id` yang tidak berubah setelah digunakan.

### 2.2 Response dipisahkan dari template

Template hanya mendefinisikan struktur. Jawaban atau data aktual harus disimpan pada instance pekerjaan tertentu. Dengan demikian, satu template dapat digunakan untuk beberapa periode, Prodi, Perguruan Tinggi, atau siklus akreditasi tanpa menimpa data lama.

### 2.3 Semua jawaban memiliki provenance

Setiap angka, narasi, skor, dan evidence harus dapat dilacak ke sumbernya: input manual, indikator SPMI, realisasi SPMI, hasil AMI, hasil import, formula, atau link evidence. Provenance adalah kebutuhan audit, bukan atribut tambahan yang boleh diabaikan.

### 2.4 Approval dan lock bersifat bisnis

Record yang sudah `approved` atau `locked` tidak boleh berubah melalui edit biasa. Revisi harus menghasilkan versi baru atau membuka proses revisi formal dengan alasan, actor, waktu, dan audit trail.

### 2.5 Link-only evidence tetap dipertahankan

SQM tidak menyimpan file fisik evidence. Sistem menyimpan URL cloud, metadata, status pemeriksaan link, pemilik, periode, citation, dan hubungan ke response/elemen/temuan.

## 3. Kondisi Fondasi Saat Ini

| Fondasi aktual | Kesiapan | Peran dalam roadmap |
|---|---|---|
| Canonical Instrument Engine | Fungsional | Sumber struktur instrumen dan versi |
| Instrument Mapping | Fungsional | Menghubungkan SPMI/AMI dengan elemen akreditasi |
| Rubric, threshold, scoring | Fungsional | Evaluasi LKE dan simulasi skor |
| Evidence Center | Fungsional | Bukti link-only dan review evidence |
| Readiness Run, Result, Gap | Fungsional sebagian | Self-assessment, gap, dan status kesiapan |
| `LedTemplate` dan `LedTemplateSection` | Fondasi tersedia | Struktur narasi LED |
| `LkpsTemplate` dan `LkpsTemplateColumn` | Fondasi tersedia | Struktur kolom borang/LKPS |
| `AccreditationResponse` | Fondasi tersedia | Penyimpanan response umum |
| Submission Package dan Score Snapshot | Fondasi tersedia | Pembekuan paket dan histori skor |
| Document Output Engine | Generik | Renderer dan riwayat output |
| SPMI/AMI/RTM/RTL | Fondasi kuat | Sumber data mutu dan tindak lanjut |

## 4. Target Domain Model

Roadmap membutuhkan beberapa lapisan baru atau perluasan model lama. Nama berikut adalah rancangan konseptual; implementasi final harus mengikuti schema aktual repository.

| Entitas | Fungsi |
|---|---|
| `AssessmentRun` atau `SelfAssessmentRun` | Satu pelaksanaan LKE/self-assessment untuk instrumen, tenant, periode, dan konteks tertentu |
| `AssessmentResponse` | Jawaban per elemen/kriteria LKE atau LED dengan status workflow |
| `AssessmentResponseRevision` | Histori revisi jawaban sebelum dan sesudah review |
| `ResponseEvidenceReference` | Citation evidence link ke response, halaman, bagian, dan catatan |
| `BorangDataset` | Satu paket data borang/LKPS untuk periode, unit, dan versi template |
| `BorangRow` | Baris data aktual pada dataset |
| `BorangValue` atau JSON tervalidasi | Nilai per kolom dengan tipe, formula, dan provenance |
| `ReviewTask` | Tugas review yang dapat digunakan untuk LKE, LED, LKPS, evidence, dan submission |
| `ApprovalRecord` | Persetujuan bisnis dan alasan perubahan status |
| `SubmissionSnapshot` | Bekuan struktur instrumen, response, data borang, evidence metadata, dan skor |
| `DocumentTemplateVersion` | Versi template resmi yang immutable |
| `DocumentGenerationRequest` | Permintaan pembuatan dokumen dan status proses |
| `DocumentArtifact` | Hasil PDF/XLSX/DOCX/ZIP atau manifest link yang dapat diunduh |

Tidak semua entitas harus langsung dibuat sebagai tabel baru. Jika `AccreditationResponse`, `ReadinessResult`, atau model document engine dapat diperluas tanpa menghilangkan kompatibilitas, gunakan model yang sudah ada.

## 5. Roadmap Utama

### Fase L0 — Contract dan Template Specification

Fase ini dilakukan sebelum coding. Tetapkan jenis output, struktur status, role reviewer, metadata wajib, dan kontrak template. Pisahkan template generik dari template resmi BAN-PT/LAM yang belum diterima dari klien.

**Output fase:** response status matrix, field dictionary, template contract, output registry, dan acceptance criteria.

### Fase L1 — LKE/LED Workspace

Bangun workspace berbasis satu `Accreditation` atau `AssessmentRun`. Pengguna dapat menelusuri hirarki instrumen, melihat elemen, mengisi jawaban, menghubungkan evidence, melihat mapping SPMI/AMI, melihat skor/readiness, dan membuka gap/RTL tanpa berpindah antar Resource secara berlebihan.

Fitur minimal meliputi draft, autosave, validasi required, indikator kelengkapan, komentar reviewer, status response, link evidence, citation page/note, dan preview narasi.

**Acceptance criteria:** satu elemen dapat diisi, disimpan, diberi evidence, dihitung statusnya, direview, dikembalikan untuk revisi, dan dilacak histori perubahan.

### Fase L2 — Response Workflow, Review, Approval, dan Lock

Terapkan status standar `draft`, `submitted`, `in_review`, `revision_required`, `approved`, `rejected`, dan `locked`. Status harus memiliki guard per role, tenant, dan konteks akreditasi.

Response yang approved tidak diedit langsung. Perubahan harus melalui `request revision` atau membuat revision baru. Approval harus menyimpan actor, waktu, catatan, dan before/after summary.

**Acceptance criteria:** reviewer dapat mengembalikan response dengan catatan; penulis dapat memperbaiki; approver dapat menyetujui; response approved tidak dapat diedit tanpa proses revisi.

### Fase L3 — Borang/LKPS Dataset Workspace

Bangun dataset borang berdasarkan `LkpsTemplate` dan `LkpsTemplateColumn`. Satu dataset memiliki versi template, tenant, Prodi/PT, periode, owner, status, dan sumber data.

Pengisian harus mendukung tabel dinamis, tipe numerik/teks/tanggal/enum, unit, required field, min/max, formula, allowed values, serta validasi lintas kolom. Data tidak boleh hanya tersimpan sebagai teks bebas.

**Acceptance criteria:** pengguna dapat membuat dataset, mengisi baris, melihat error per sel, menghitung formula, mengirim untuk review, dan melihat ringkasan kelengkapan.

### Fase L4 — Import dan Reconciliation Data

Sediakan import CSV/XLSX tanpa API eksternal. Proses wajib melewati staging: upload file, pilih template, mapping kolom, preview, validasi, daftar error per baris, perbaikan, submit import, approval, dan ledger.

Sumber data dapat berupa SIAKAD, SDM, keuangan, penelitian, pengabdian, perpustakaan, tracer study, atau input internal. Sistem mencatat source system, periode, waktu import, actor, dan checksum file sumber bila file tersebut hanya digunakan sebagai sumber sementara.

**Acceptance criteria:** import yang memiliki error tidak langsung mengubah dataset final; import yang approved dapat diulang secara idempotent dan seluruh perubahan dapat ditelusuri.

### Fase L5 — Provenance dan Dynamic Validation Engine

Hubungkan response dan dataset dengan indikator SPMI, realisasi, hasil AMI, evidence, formula, serta mapping instrumen. Tampilkan source badge pada setiap nilai atau narasi.

Validasi dijalankan di backend service dan UI. Rule mencakup required, tipe, rentang, allowed values, formula, konsistensi total, periode, satuan, uniqueness, dan dependency antar kolom.

**Acceptance criteria:** sistem dapat menjelaskan asal nilai, rule yang gagal, actor sumber, periode, dan evidence pendukung.

### Fase L6 — Penguatan Document Output Engine

Document engine diubah dari laporan generik menjadi registry output berbasis tipe dokumen. Setiap output memiliki code, context, renderer, template version, permission, status, dan daftar dependency.

Output tahap awal yang dapat dikerjakan tanpa template resmi adalah:

| Output | Format awal | Sumber |
|---|---|---|
| Katalog indikator IKU/IKT | PDF/XLSX | SPMI indicators dan targets |
| Laporan capaian standar | PDF | SPMI realization/evaluation |
| Laporan komprehensif AMI | PDF | AMI cycle, checklist, finding |
| Notula/risalah RTM | PDF | RTM meeting dan decisions |
| Matriks RTL | PDF/XLSX | RTL actions dan status |
| Status closing temuan | PDF | Finding, verification, evidence |
| Matriks simulasi skor | PDF/XLSX | Readiness dan scoring |
| Evidence matrix | XLSX/ZIP manifest | Link evidence dan mapping |

ZIP pada evidence matrix berarti paket manifest dan metadata/link, bukan upload ulang file fisik ke server SQM.

### Fase L7 — Official Template Adapter

Setelah klien memberikan blanko resmi, buat adapter per versi template. Adapter bertugas mengisi cell Excel, heading Word, tabel, nomor halaman, dan field yang ditentukan template tanpa mengubah engine domain.

Target output meliputi LKPS XLSX resmi, LED DOCX resmi, berita acara PDF, surat tugas PDF, worksheet audit, dan submission package. Official template harus memiliki immutable version, checksum, compatibility rule, dan test fixture.

**Acceptance criteria:** output menggunakan blanko yang tepat, field tidak bergeser, formula dan format dipertahankan, dan output dapat dibandingkan dengan template melalui regression test.

### Fase L8 — Submission Snapshot dan Package Lock

Sebelum generate final, sistem membuat snapshot yang memuat versi instrumen, template, response LED/LKE, dataset LKPS, evidence metadata, readiness score, gap, dan approval. Setelah package locked, perubahan master tidak mengubah artifact lama.

Package harus memiliki checklist kelengkapan, manifest, status approval, checksum artifact, actor, waktu, dan alasan reopen bila dilakukan.

### Fase L9 — QA, Security, Performance, dan Rollout

Lakukan UAT per role dan tenant, pengujian kebocoran data, test lifecycle, test import, test formula, test template rendering, load test report generation, queue failure test, backup/restore test, dan pilot pada satu PT/Prodi.

## 6. Roadmap Output Dokumen Berdasarkan Dependency

| Gelombang | Output | Dependency |
|---|---|---|
| Gelombang 1 | Katalog IKU/IKT, capaian standar, laporan AMI, matriks RTL | Data SPMI/AMI/RTL yang sudah ada |
| Gelombang 2 | Notula RTM, closing finding, readiness/scoring, evidence matrix | RTM, evidence, readiness, document registry |
| Gelombang 3 | Workspace LKE/LED dan draf LED generik | Response workflow dan evidence citation |
| Gelombang 4 | Dataset LKPS dan export XLSX generik | Borang dataset, formula, validation |
| Gelombang 5 | LKPS/LED resmi per LAM/BAN-PT | Blanko resmi dan adapter version |
| Gelombang 6 | Submission package locked dan archive | Approval, snapshot, checksum, QA |

## 7. Permission dan Role

| Kemampuan | Role yang umumnya membutuhkan |
|---|---|
| Membuat/edit draft response | `quality_manager`, `lpm`, `kaprodi`, `pt_admin` sesuai tenant |
| Meninjau response | `reviewer`, `instrument_reviewer`, `lpm` |
| Menyetujui instrumen/rubric | `instrument_approver`, `quality_manager`, `super_admin` |
| Menyetujui LED/LKPS | `lpm`, pimpinan sesuai matriks UAT |
| Mengunci submission | `instrument_approver` atau approver submission |
| Generate laporan | Role yang memiliki akses reporting/submission |
| Melihat seluruh tenant | Hanya `super_admin` sesuai aturan keamanan |

Permission harus dipisahkan antara `view`, `create`, `update`, `submit`, `review`, `approve`, `lock`, `export`, dan `reopen`. Permission `update` tidak boleh secara otomatis memberi hak `approve` atau `lock`.

## 8. Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Format LAM berubah | Immutable instrument/template version dan adapter per versi |
| Nilai borang tidak konsisten | Data dictionary, formula, validation, dan provenance |
| Evidence cloud tidak bisa diakses | Link health check, expiry reminder, dan owner |
| Dokumen historis berubah | Submission snapshot dan lock |
| Template resmi berbeda antar instrumen | Template adapter, bukan hard-code ke model domain |
| Import merusak data final | Staging, preview, validation, approval, dan idempotency |
| Kebocoran tenant | Query model/resource, policy record-level, dropdown, dan test cross-tenant |
| Output gagal saat produksi | Queue, retry, failed job, artifact status, dan monitoring |

## 9. Urutan Implementasi yang Disarankan

Urutan yang paling aman adalah menyelesaikan **L1 dan L2** terlebih dahulu, karena LKE/LED menjadi pusat narasi dan review. Setelah itu kerjakan **L3 dan L4** untuk membuat LKPS menjadi data aktual. **L5** memastikan semua jawaban dapat dipertanggungjawabkan. **L6** menghasilkan laporan generik yang dapat langsung diuji. **L7 dan L8** baru dikerjakan setelah template resmi dan kebutuhan klien tersedia.

Tidak disarankan langsung membangun semua output dalam lampiran sekaligus. Output harus mengikuti dependency data dan governance. Dokumen yang mudah dibuat dari data SQM saat ini dapat dikerjakan lebih dulu; dokumen resmi LED/LKPS menunggu blanko yang disepakati.

## 10. Definisi Selesai

Modul LKE/Borang dinyatakan siap tahap pra-produksi apabila pengguna dapat memilih versi instrumen, mengisi response atau dataset, melampirkan link evidence, melihat validasi dan readiness, mengirim untuk review, menerima revisi, menyetujui, mengunci, menghasilkan snapshot, dan mengekspor output.

Document Output Engine dinyatakan siap tahap pra-produksi apabila setiap request memiliki context, template version, permission, status, log error, artifact, checksum, preview, download, dan tidak dapat mengubah snapshot historis.

## 11. Keputusan Arsitektur yang Perlu Disimpan

Pertama, LKE dan borang tidak dibuat sebagai dua engine terpisah. Keduanya menggunakan canonical instrument dan response/dataset layer yang dapat dikonfigurasi.

Kedua, LED dan LKPS adalah bentuk output dan workspace yang berbeda di atas versi instrumen yang sama, bukan sekadar dua tabel template.

Ketiga, Evidence Center tetap menyimpan link cloud saja. Sistem menyimpan metadata, citation, status akses, dan histori, bukan file fisik.

Keempat, output generik dapat diimplementasikan lebih dahulu. Output resmi BAN-PT/LAM hanya dibuat setelah blanko dan versi instrumen ditetapkan.

Kelima, submission harus immutable melalui snapshot dan lock. Data aktif boleh berkembang, tetapi artifact historis tidak boleh berubah.
