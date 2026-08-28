# Audit Kesiapan SQM terhadap Rancangan SPMI–AMI–Akreditasi

## Kesimpulan

Source SQM saat ini sudah mendukung sebagian besar **fondasi arsitektur** rancangan pada attachment, tetapi belum seluruh **otomasi operasional** yang digambarkan di dalamnya. Sistem sudah tepat menggunakan satu platform modular dengan tenant context, RBAC, SPMI, AMI, PPEPP, evidence link-only, instrument versioning, mapping, readiness scoring, LED/LKPS, approval, audit, dan queue.

Kesenjangan terbesar bukan pada kemampuan menyimpan atau menghubungkan data, melainkan pada tahap lanjutan: roll-up data dari program studi ke institusi, auto-populate LED/LKPS/LKPT, workspace task force, conflict-of-interest guard, provenance angka/narasi, export profile resmi per instrumen, dan integrasi sumber data akademik/PDDikti.

## Status setiap gagasan

| Gagasan | Status di SQM saat ini | Penilaian |
|---|---|---|
| Satu platform untuk SPMI, AMI, dan Akreditasi | Panel Filament dan domain aplikasi terintegrasi | Didukung |
| Satu login dengan role berbeda | RBAC dan permission tersedia | Didukung secara fondasi |
| SPMI sebagai sumber standar dan target | Modul SPMI memiliki standard, indicator, target, realization, evaluation | Didukung |
| AMI sebagai bagian evaluasi SPMI | Modul AMI dan alur finding tersedia | Didukung |
| PPEPP sampai improvement | SpmiImprovementProgram, RTL, effectiveness review, dan feedback loop tersedia | Didukung |
| Auditor dan pendamping dipisahkan | Role tersedia, tetapi konflik kepentingan belum diblokir eksplisit | Sebagian |
| Satu evidence digunakan untuk banyak konteks | Evidence link-only, polymorphic link, review, history check, dan mapping tersedia | Didukung |
| Evidence yang belum verified tidak dipakai scoring | Readiness dan scoring memeriksa validitas evidence | Didukung |
| SPMI dipetakan ke BAN-PT/LAM | InstrumentMapping dan approval workflow tersedia | Didukung |
| SPMI tidak dipaksa sama dengan struktur eksternal | Instrument engine versioned terpisah dari SPMI | Didukung secara arsitektur |
| Instrumen 9, 6, atau format LAM berbeda | Instrument family/version, node hierarchy, scale, rubric, threshold | Didukung |
| Descriptor skor per indikator | Canonical import-v2 dan rubric/scale option tersedia | Didukung |
| Threshold bertingkat | AssessmentThreshold dan evaluator canonical tersedia | Didukung |
| Rule Unggul | status_qualification sudah dieksekusi runtime | Didukung untuk konfigurasi yang sudah diimpor/approved |
| Akreditasi institusi | Aggregate mendukung scope institution | Didukung sebagai fondasi |
| Akreditasi program studi | Aggregate mendukung scope program study | Didukung sebagai fondasi |
| Filtering data berdasarkan prodi | Tenant/program-study access dan global search sudah tenant-aware | Didukung sebagian |
| Roll-up LKPT dari seluruh prodi | Belum ada engine agregasi versioned yang lengkap | Belum selesai |
| Workspace task force per kriteria | Belum ada assignment workspace granular | Belum selesai |
| Auto-populate LED dari AMI | Evidence dan response dapat dihubungkan, tetapi pipeline candidate → review → accept belum lengkap | Belum selesai |
| Auto-populate LKPS dari data angka | Template dan column tersedia, tetapi connector/aggregation otomatis belum lengkap | Belum selesai |
| Auto-populate LED Institusi | Belum ada roll-up narasi lintas prodi | Belum selesai |
| Simulasi skor/gap | Readiness scoring dan snapshot tersedia | Didukung V1 |
| Dashboard readiness | Widget readiness, progress, PPEPP, dan verified program tersedia | Didukung dasar |
| Grafik IKU/IKT lintas tahun | Belum ada data ingestion/metric registry lengkap untuk semua IKU/IKT | Sebagian |
| RTL otomatis dari temuan | Finding → RTM/RTL sudah tersedia | Didukung |
| Gap terkunci sampai closed | Gap resolution dan evidence completion tersedia, tetapi lock lintas siklus perlu diverifikasi per alur | Sebagian |
| Import legacy | Legacy dry-run/import dengan checksum, ledger, exception, dan idempotency | Didukung |
| Dual-run | Ledger dan dry-run tersedia, tetapi rekonsiliasi source lama vs SQM belum menjadi workflow penuh | Sebagian |
| Export PDF/Excel generik | Accreditation report export tersedia | Didukung |
| Export format resmi tiap LAM/BAN-PT | Belum ada export profile cell/sheet mapping per instrument version | Belum selesai |
| Link cloud dan QR/hyperlink pada dokumen | URL tersimpan; export resmi dengan hyperlink/QR belum lengkap | Sebagian |
| Integrasi PDDikti/Feeder | Ditunda sesuai keputusan pengguna | Belum diaktifkan |
| Health check dan queue | `sqm:health`, database queue, retry, backoff, dan worker tersedia | Didukung |
| Backup/restore dan staging | Checklist dokumentasi tersedia; backup otomatis dan restore drill operasional belum tersedia | Sebagian |

## Analisis arsitektur

### 1. Hubungan SPMI dan AMI

Rancangan attachment menyatakan bahwa SPMI dan AMI tidak boleh dipisah secara konseptual. Arsitektur SQM sudah sesuai. SPMI menyediakan standar, indikator, target, dan realisasi. AMI menguji realisasi terhadap standar. Temuan AMI dapat mengalir ke RTM, RTL, effectiveness review, dan PPEPP.

Pemisahan yang diperlukan adalah pemisahan **kewenangan dan personel**, bukan pemisahan database. SQM sudah memiliki dasar RBAC, tetapi perlu aturan tambahan agar auditor tidak menjadi approver atau pendamping pada objek audit yang sama.

### 2. Single source of truth dan evidence reuse

Gagasan bahwa satu dokumen dapat menjadi bukti standar SPMI, bukti temuan AMI, dan bukti akreditasi sudah kompatibel dengan Evidence Center SQM. Implementasi link-only juga lebih sesuai dengan kebutuhan institusi yang menggunakan Google Drive atau cloud storage sendiri.

Yang perlu dipastikan pada pengembangan berikutnya adalah status penggunaan per konteks. Satu URL yang sama dapat memiliki review AMI yang berbeda dari review akreditasi. Oleh karena itu, validitas evidence harus disimpan pada relasi evidence link/review, bukan hanya pada dokumen global.

### 3. Akreditasi institusi dan prodi

SQM sudah memiliki fondasi aggregate yang membedakan scope `institution` dan `program_study`. Ini cukup untuk mendukung dua jenis workspace dengan instrument version berbeda.

Namun, dukungan ini belum sama dengan roll-up LKPT lengkap. Roll-up memerlukan definisi formula, periode, daftar source record, filter unit/prodi, snapshot nilai, dan approval. Tanpa itu, angka institusi masih berisiko dianggap sebagai input manual atau agregasi ad hoc.

### 4. Sembilan atau enam kriteria BAN-PT

Implementasi SQM sudah menggunakan pendekatan yang benar: jumlah dan struktur kriteria ditentukan oleh `instrument_version`, bukan oleh kode global. Karena itu, instrumen lama dan baru dapat hidup berdampingan.

Informasi pada attachment mengenai perubahan jumlah kriteria harus tetap diperlakukan sebagai konteks bisnis, bukan aturan global. Setiap versi harus dikonfigurasi berdasarkan dokumen resmi, tanggal berlaku, scope, dan status publish.

### 5. LAM dan instrumen khusus

LAM dapat ditambahkan sebagai `instrument_family` dan `instrument_version`, sedangkan kriteria, elemen, indikator, skala, rubric, threshold, qualification rule, LED, dan LKPS berada di bawah versi tersebut. Canonical import-v2 sudah mendukung descriptor, threshold bertingkat, dan rule status qualification.

Dengan demikian, menambahkan LAM baru tidak membutuhkan pembuatan modul coding baru. Yang diperlukan adalah manifest yang tervalidasi, mapping internal, test scoring, approval, dan publish.

## Gap prioritas

### Prioritas pertama: Roll-Up Engine Institusi

Engine ini diperlukan agar LKPT dapat dibentuk dari data prodi/unit secara transparan. Setiap definisi agregasi minimal harus menyimpan `source_entity`, `source_scope`, `filter`, `period`, `aggregation`, `formula_version`, `calculated_at`, dan daftar provenance.

### Prioritas kedua: Auto-populate berbasis kandidat

Data AMI, realization, evidence verified, mapping approved, dan hasil scoring harus menghasilkan kandidat pengisian LED/LKPS. Kandidat tidak langsung menjadi jawaban final. Status yang disarankan adalah `suggested`, `accepted`, `rejected`, dan `locked`.

### Prioritas ketiga: Workspace dan assignment

Tim task force perlu ditugaskan pada kriteria, section LED, atau kolom LKPS tertentu. Permission harus membatasi edit sesuai assignment dan tetap memberi LPM akses monitoring lintas bagian.

### Prioritas keempat: Conflict-of-interest guard

Sistem perlu memeriksa kombinasi auditor, auditee, pendamping, reviewer, dan approver. Minimal terdapat warning; untuk proses kritis, sistem sebaiknya memblokir approval jika actor memiliki assignment yang konflik.

### Prioritas kelima: Export profile per versi

Exporter generik belum cukup untuk kebutuhan LAM/BAN-PT. Setiap versi instrumen perlu memiliki konfigurasi sheet, kolom, cell, format angka, periode, formula, dan hyperlink evidence.

### Prioritas keenam: Provenance dan data ingestion

Sebelum PDDikti diaktifkan, SQM dapat menyiapkan import batch dari sistem akademik. Setiap angka harus memiliki sumber, periode, checksum/batch, actor, status validasi, dan waktu import.

## Koreksi dan kehati-hatian terhadap isi attachment

Attachment mengandung rancangan bisnis yang berguna, tetapi beberapa pernyataan regulasi atau status instrumen bersifat time-sensitive. Sistem tidak boleh mengunci asumsi bahwa semua BAN-PT selalu memakai sembilan atau enam kriteria, atau bahwa seluruh akreditasi selalu berjalan dengan mekanisme yang sama. Kebenaran operasional harus berasal dari instrument version dan dokumen resmi yang dipilih LPM.

Pernyataan bahwa aplikasi dapat mengisi 80% borang secara otomatis juga sebaiknya diperlakukan sebagai target produk, bukan jaminan teknis. Persentase tersebut bergantung pada kualitas mapping, kelengkapan data, validitas evidence, sumber angka, dan proses review manusia.

## Kesimpulan akhir

**SQM saat ini sudah mendukung rancangan tersebut pada tingkat fondasi arsitektur dan sebagian besar alur inti.** Rancangan baru tidak menuntut pembongkaran total sistem. Ia membutuhkan penguatan pada lapisan agregasi, orkestrasi data, workspace, provenance, konflik peran, dan export profile.

Urutan implementasi yang paling aman adalah:

```text
Roll-Up Engine Institusi
        ↓
Data Provenance dan Data Ingestion Batch
        ↓
Auto-Populate LED/LKPS/LKPT berbasis kandidat
        ↓
Workspace Task Force dan Assignment
        ↓
Conflict-of-Interest Guard
        ↓
Export Profile per Instrument Version
        ↓
Integrasi PDDikti/Feeder jika sudah disetujui
```

Dengan urutan ini, fondasi yang telah dibuat tetap digunakan dan perubahan dapat diuji per modul tanpa mengganggu scoring, evidence, SPMI, AMI, atau workflow yang sudah berjalan.
