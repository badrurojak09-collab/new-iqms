# Analisis Integrasi Percakapan SPMI, AMI, Akreditasi, dan SPME ke Platform SQM

## Kesimpulan eksekutif

Gagasan utama dalam percakapan dapat diintegrasikan ke SQM. Bahkan, sebagian besar fondasi teknisnya sudah tersedia: multi-tenant, instrument versioning, pemetaan indikator internal ke elemen eksternal, evidence berbasis tautan cloud, readiness scoring, LED/LKPS, RTL/RTM, PPEPP, approval, audit log, dan snapshot skor immutable.

Namun, integrasi penuh belum berarti semua alur otomatis sudah selesai. Gap terbesar berada pada **roll-up data institusi**, **auto-populate LED/LKPT/LKPS dari data sumber**, **workspace berbasis kriteria**, **pemisahan kewenangan pendamping dan auditor**, **data provenance**, dan **konektor data akademik/PDDikti**. Integrasi PDDikti sebelumnya memang ditunda, sehingga harus diperlakukan sebagai backlog terpisah dan tidak boleh dianggap sudah aktif.

## 1. Prinsip organisasi dan arsitektur

Percakapan menyimpulkan bahwa SPMI, AMI, dan pendampingan akreditasi idealnya berada dalam satu ekosistem, tetapi dapat menggunakan personel dan kewenangan berbeda. Kesimpulan ini tepat.

SPMI dan AMI tidak perlu dipisahkan sebagai sumber data yang berdiri sendiri. SPMI menyediakan standar, indikator, target, realisasi, dan siklus PPEPP. AMI menggunakan standar tersebut untuk evaluasi independen. Akreditasi/SPME menggunakan hasil yang telah tervalidasi sebagai bahan LED, LKPS, LED Institusi, dan LKPT.

Di tingkat aplikasi, pemisahan tersebut sebaiknya diwujudkan melalui **domain dan authorization boundary**, bukan melalui database silo atau tiga aplikasi yang menggandakan data.

```text
SPMI: standar, indikator, target, realisasi
              │
              ▼
AMI: evaluasi, auditor, temuan, rekomendasi
              │
              ▼
RTM/RTL/PPEPP: keputusan, tindakan, verifikasi, peningkatan
              │
              ▼
Akreditasi/SPME: LED, LKPS/LKPT, readiness, submission
```

Satu dokumen atau satu data kinerja dapat dipakai di beberapa konteks melalui relasi dan mapping. Dokumen tidak perlu diunggah ulang, tetapi penggunaannya pada setiap konteks harus tetap memiliki status review, validity, dan audit trail sendiri.

## 2. Pemetaan gagasan ke source SQM

| Gagasan pada percakapan | Dukungan pada SQM saat ini | Status integrasi |
|---|---|---|
| Satu login dan RBAC multi-peran | Filament panel, role/permission, tenant context | Fondasi tersedia |
| SPMI sebagai hulu | Model standar, indikator, target, realisasi, evaluasi, PPEPP | Tersedia secara fungsional |
| AMI sebagai evaluasi SPMI | AMI, temuan, RTM, RTL, effectiveness review | Tersedia secara fungsional |
| Evidence sekali simpan dan dipakai lintas konteks | Evidence Center, link-only, polymorphic linking, history check | Tersedia |
| Mapping SPMI/AMI ke BAN-PT/LAM | `InstrumentMapping`, approval workflow, readiness linkage | Tersedia |
| Instrumen dinamis dan versioned | Instrument family/version, node hierarchy, scale, rubric, threshold | Tersedia dan diperluas |
| Rule Unggul LAM INFOKOM | `status_qualification` pada runtime evaluator | Sudah diimplementasikan |
| LED/LKPS berbasis template | LED/LKPS template dan relation manager | Fondasi tersedia |
| Simulasi readiness | Readiness scoring dan immutable score snapshot | Tersedia versi V1 |
| RTL menutup gap | Gap → RTL → evidence completion → resolve | Tersedia secara fungsional |
| Dashboard mutu | Widget readiness, PPEPP, verified program, progress | Tersedia secara dasar |
| Roll-up LKPT institusi | Belum ditemukan implementasi agregasi lintas prodi yang lengkap | Gap utama |
| Workspace tim per kriteria | Belum tersedia sebagai workspace granular | Gap |
| Auto-populate LED/LKPS dari AMI | Belum tersedia sebagai pipeline penuh dan terdokumentasi | Gap |
| PDDikti/Feeder/SIAKAD | Integrasi eksternal ditunda | Belum diaktifkan |
| Ekspor persis format resmi setiap LAM | Ekspor laporan generik tersedia; template resmi per LAM belum lengkap | Gap |
| Pilot dan go-live | Checklist dan health check tersedia | Baseline, belum pilot nyata |

## 3. SPMI, AMI, dan akreditasi tidak boleh menjadi struktur yang sama

SPMI tidak seharusnya dipaksa mengikuti Kriteria 1–9 BAN-PT atau struktur LAM. Standar internal kampus dapat mengikuti SN-Dikti, Statuta, Renstra, standar tambahan, atau diferensiasi misi perguruan tinggi.

Hubungan yang benar adalah mapping:

```text
SPMI Standard
   └── SPMI Indicator
          ├── Evidence
          ├── AMI Evaluation
          ├── AMI Finding
          ├── RTL/RTM
          └── Instrument Mapping
                    └── External Element / Indicator
```

Dengan pola ini, satu indikator internal dapat dipetakan ke beberapa versi instrumen. Contohnya, indikator publikasi dosen dapat dipetakan ke elemen BAN-PT, LAMEMBA, dan LAM INFOKOM dengan bobot, evidence expectation, dan threshold yang berbeda.

## 4. Entitas yang harus terhubung ke kriteria eksternal

Percakapan mengidentifikasi empat kelompok sumber yang tepat.

| Kelompok | Contoh entitas | Cara koneksi |
|---|---|---|
| Dokumen/evidence | SK, kebijakan, kurikulum, renstra, laporan, notulen | Evidence link atau mapping polymorphic ke node/element/response |
| Data kuantitatif | Dosen, mahasiswa, lulusan, penelitian, publikasi, keuangan | Source record, indicator realization, formula, atau data ingestion |
| Data kualitatif | Analisis, evaluasi diri, SWOT, akar masalah | Accreditation response dan LED section |
| Hasil mutu | Evaluasi AMI, temuan, rekomendasi, RTL, effectiveness review | Relasi ke SPMI indicator dan external element melalui mapping |

Relasi tersebut jangan hanya berupa tag bebas. Untuk kebutuhan audit, mapping perlu memiliki `source_type`, `source_id`, `target_instrument_version_id`, `target_element_id`, `coverage`, `approval_status`, `valid_from`, `valid_until`, dan `source_reference`.

## 5. Akreditasi institusi versus akreditasi program studi

Percakapan sudah membedakan dua level dengan benar, tetapi istilahnya perlu dipakai konsisten.

| Level | Unit yang dinilai | Data utama | Dokumen keluaran |
|---|---|---|---|
| Institusi/PT | Perguruan tinggi secara keseluruhan | Data agregat universitas dan data lintas unit | LKPT dan LED Institusi |
| Program Studi | Satu program studi | Data spesifik prodi, capaian lulusan, kurikulum, SDM prodi | LKPS dan LED Prodi |

Keduanya harus memakai engine instrumen yang sama, tetapi memiliki `scope_type` dan `scope_id` berbeda. Akreditasi institusi memakai scope perguruan tinggi dan dapat membaca data dari prodi-prodi di bawahnya melalui aturan agregasi. Akreditasi prodi memakai scope program studi dan hanya boleh membaca data yang sah untuk prodi tersebut.

## 6. Roll-up data institusi yang masih perlu dibangun

Gagasan roll-up adalah integrasi penting yang belum boleh dianggap selesai hanya karena model institusi dan prodi sudah ada. Roll-up harus dibuat sebagai definisi agregasi versioned.

Contoh definisi:

```json
{
  "code": "LKPT-SDM-S3",
  "scope": "institution",
  "source_entity": "lecturer_records",
  "source_scope": "all_program_studies",
  "filter": {"education_level": "S3", "active": true},
  "aggregation": "count_distinct",
  "period": "academic_year",
  "provenance_required": true
}
```

Hasil roll-up tidak sebaiknya langsung menimpa data manual. Sistem harus menyimpan:

```text
source period
source records
filter
formula version
calculated value
calculated at
calculated by/job
approval status
```

Dengan begitu, LKPT dapat menampilkan angka agregat sekaligus jejak sumbernya.

## 7. Auto-populate LED, LKPS, LKPT, dan LED Institusi

Auto-populate dapat diintegrasikan, tetapi harus menggunakan sistem kandidat dan approval, bukan menulis langsung ke dokumen final.

```text
AMI evaluation / verified realization / approved evidence
                 │
                 ▼
      Mapping + scope filter + period filter
                 │
                 ▼
       Candidate value/narrative/evidence
                 │
                 ▼
        Review oleh task force atau LPM
                 │
                 ▼
       Accepted response pada LED/LKPS/LKPT
```

Untuk LED, sistem dapat mengusulkan narasi dari:

- capaian indikator;
- status temuan AMI;
- kekuatan dan kelemahan;
- akar masalah;
- RTL dan efektivitas perbaikan;
- evidence yang sudah diverifikasi.

Narasi tetap perlu ditinjau manusia. Sistem tidak boleh menyatakan bahwa narasi otomatis adalah dokumen final tanpa approval.

## 8. Workspace dan independensi peran

Kebutuhan pemisahan personel dapat direalisasikan dengan role dan assignment:

| Peran | Hak utama |
|---|---|
| LPM/Admin mutu | Mengelola standar, instrumen, mapping, assignment, monitoring |
| Auditee/Prodi | Mengisi data, evaluasi diri, menautkan evidence, merespons temuan |
| Auditor AMI | Mengisi audit, skor, temuan, rekomendasi; tidak mengubah data auditee secara bebas |
| Reviewer/Asesor internal | Meninjau readiness dan evidence |
| Pendamping akreditasi | Membantu LED/LKPS; tidak menyetujui auditnya sendiri |
| Approver | Menyetujui konfigurasi, evidence, hasil review, atau submission |
| Task force kriteria | Mengedit workspace kriteria yang ditugaskan |

Aturan konflik kepentingan harus memblokir atau setidaknya memberi peringatan ketika auditor yang ditugaskan pada AMI yang sama juga menjadi approver pendampingan pada objek tersebut.

## 9. Sembilan versus enam kriteria BAN-PT

Bagian percakapan yang menyebut perubahan universal dari sembilan menjadi enam kriteria perlu diperlakukan hati-hati. Di aplikasi, jangan membuat asumsi bahwa semua institusi atau semua periode harus memakai sembilan atau enam kriteria.

Implementasi yang benar adalah:

```text
Accreditation body
   └── Instrument family
          └── Instrument version
                 ├── criteria count
                 ├── scope type
                 ├── effective dates
                 ├── scoring rules
                 └── qualification rules
```

Artinya, instrumen institusi BAN-PT versi tertentu dapat memiliki struktur dan jumlah kriteria sendiri, sementara instrumen prodi LAM memiliki struktur lain. Sistem harus menampilkan hanya versi instrumen yang sesuai dengan scope, jenjang, rumpun, periode berlaku, dan status publikasinya.

Klaim tentang regulasi, masa berlaku, mekanisme automasi, dan format resmi harus selalu divalidasi terhadap dokumen resmi yang berlaku pada saat konfigurasi dibuat. Percakapan tersebut berguna sebagai rancangan bisnis, tetapi tidak boleh diperlakukan sebagai sumber hukum final.

## 10. PDDikti dan data eksternal

Gagasan triangulasi PDDikti penting, tetapi integrasi API/PDDikti sebelumnya telah ditunda. Karena itu, baseline SQM saat ini harus membedakan tiga status sumber data:

| Sumber | Status |
|---|---|
| Input manual terkontrol | Dapat digunakan dengan reviewer dan evidence |
| Import CSV/XLSX dari sistem akademik | Dapat menjadi tahap antara |
| API PDDikti/Feeder | Backlog dan belum aktif |

Sebelum konektor eksternal dibangun, sistem dapat menyiapkan interface data ingestion, source batch, checksum, period, imported_by, dan validation result. Ini memungkinkan integrasi PDDikti nanti tanpa mengubah indikator atau template akreditasi.

## 11. Output dokumen yang harus disediakan

Percakapan mengidentifikasi keluaran yang tepat, tetapi format final perlu dibedakan antara dokumen internal dan template resmi.

| Rumpun | Output |
|---|---|
| SPMI | Kebijakan mutu, standar, indikator, target, laporan capaian, rapor mutu |
| AMI | KKA/Kertas Kerja Audit, LHA, daftar temuan, rekomendasi |
| RTM/RTL | Risalah RTM, RTL, bukti penyelesaian, effectiveness review |
| APS | LKPS dan LED prodi |
| AIPT | LKPT dan LED institusi |
| Readiness | Simulasi skor, gap report, evidence coverage, qualification result |
| Submission | Paket dokumen dan manifest link/evidence |

Ekspor generik yang sudah tersedia belum sama dengan ekspor yang dijamin identik dengan template resmi setiap LAM. Untuk produksi, setiap `instrument_version` perlu memiliki export profile yang mengatur sheet, cell/column mapping, format angka, periode, dan validasi.

## 12. Prioritas integrasi ke source SQM

### Prioritas 1: Roll-up engine institusi

Bangun definisi agregasi dan hasil snapshot untuk LKPT. Ini adalah gap paling strategis karena membedakan akreditasi institusi dari akreditasi prodi.

### Prioritas 2: Source provenance

Tambahkan jejak sumber ke setiap angka dan narasi hasil auto-populate: periode, query/definition, sumber record, reviewer, dan waktu kalkulasi.

### Prioritas 3: Auto-populate berbasis kandidat

Hubungkan AMI, realization, evidence verified, mapping approved, dan LED/LKPS response dengan mode `suggested`, `accepted`, `rejected`, dan `locked`.

### Prioritas 4: Workspace dan assignment

Tambahkan assignment per kriteria/section/column dan cegah task force mengedit bagian di luar kewenangannya.

### Prioritas 5: Conflict-of-interest guard

Blokir atau beri peringatan terhadap kombinasi auditor, pendamping, reviewer, dan approver yang tidak independen.

### Prioritas 6: Export profile per instrument version

Jangan membuat satu exporter universal. Gunakan konfigurasi ekspor per versi LAM/BAN-PT.

### Prioritas 7: Data ingestion abstraction

Siapkan import batch untuk data akademik sebelum mengaktifkan integrasi PDDikti/Feeder.

## Kesimpulan

Poin-poin percakapan **dapat diintegrasikan** ke SQM dan sebagian besar fondasinya sudah ada. Arsitektur yang telah dibuat sudah tepat karena SPMI menjadi sumber internal, AMI menjadi proses evaluasi independen, RTM/RTL/PPEPP menjadi loop perbaikan, dan akreditasi menjadi konsumen hilir yang membaca data tervalidasi.

Yang belum boleh diklaim selesai adalah roll-up LKPT, auto-populate LED/LKPS/LKPT secara penuh, workspace task force, conflict-of-interest enforcement, provenance data, export profile resmi per LAM, dan integrasi PDDikti. Semua itu dapat ditambahkan tanpa membongkar fondasi utama, dengan syarat tetap mempertahankan prinsip **single source of truth, versioned instrument, scope-aware access, verified evidence, dan immutable snapshot**.
