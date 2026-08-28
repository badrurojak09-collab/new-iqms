# Blueprint Masa Depan dan Prioritas Stabilisasi SQM

## Keputusan pengembangan

Rancangan integrasi SPMI, AMI, PPEPP, RTM/RTL, akreditasi institusi, akreditasi program studi, BAN-PT/LAM, roll-up, auto-populate, workspace, provenance, dan data ingestion disimpan sebagai **blueprint backlog resmi**.

Untuk tahap sekarang, source tidak diperluas dengan fitur baru besar. Prioritas utama adalah memastikan baseline SQM yang sudah dibangun dapat berjalan stabil, mudah diuji, dan bebas dari error pada menu serta workflow inti.

> Prinsip kerja: **stabilkan baseline terlebih dahulu, kemudian tambahkan capability baru secara bertahap dan teruji.**

## Baseline yang harus stabil

| Kelompok | Fitur yang harus dapat diuji |
|---|---|
| Fondasi | Login, panel admin, locale Bahasa Indonesia, konfigurasi environment, migration, cache, dan storage |
| Multi-tenant | Yayasan, perguruan tinggi, program studi, user scope, tenant context, dan isolasi query |
| RBAC | Role, permission, policy, assignment, dan pembatasan akses per scope |
| Instrument | Family, version, criteria, elements, indicators, scale, rubric, threshold, import canonical-v2, approval, dan publish |
| SPMI | Standard, indicator, target, realization, evaluation, serta siklus PPEPP |
| AMI | Audit, finding, scoring, rekomendasi, RTM, RTL, dan evidence completion |
| Evidence | Cloud URL, metadata, review status, history check, dan link reuse lintas konteks |
| Akreditasi | Institusi/prodi, LED, LKPS, response, readiness, scoring rule, qualification rule, dan snapshot |
| Queue | Verification improvement program, readiness re-evaluation, retry, failure tracking, dan worker |
| Dashboard | Metric cards, progress LED/LKPS, PPEPP chart, verified program chart, dan laporan |
| Output | Report PDF/Excel generik, audit log, dan export hasil scoring |

## Blueprint implementasi masa depan

### Tahap A — Roll-Up Engine Institusi

Membangun definisi agregasi versioned untuk LKPT dan metrik institusi. Setiap hasil harus menyimpan periode, filter, formula, source record, waktu kalkulasi, dan status review. Data agregat tidak boleh menimpa data sumber.

### Tahap B — Data provenance dan ingestion batch

Menyediakan batch import dari sistem akademik atau sumber internal sebelum integrasi PDDikti. Setiap batch memiliki checksum, periode, sumber, pemilik data, hasil validasi, dan audit trail.

### Tahap C — Auto-populate LED/LKPS/LKPT

Membuat pipeline kandidat dari data AMI, realisasi terverifikasi, evidence approved, mapping approved, dan hasil scoring. Kandidat harus melewati status `suggested`, `accepted`, `rejected`, dan `locked` sebelum menjadi isi final.

### Tahap D — Workspace task force

Menambahkan assignment per kriteria, section LED, atau kolom LKPS. Pengguna hanya dapat mengedit bagian yang ditugaskan, sementara LPM dapat memonitor progres lintas bagian.

### Tahap E — Conflict-of-interest guard

Menerapkan pemeriksaan konflik antara auditor, auditee, pendamping, reviewer, dan approver. Untuk proses kritis, konflik harus memblokir approval atau memerlukan override beralasan.

### Tahap F — Export profile per instrument version

Membuat konfigurasi ekspor per versi instrumen yang mengatur sheet, cell/column mapping, format angka, periode, formula, dan hyperlink evidence.

### Tahap G — Integrasi PDDikti/Feeder

Dilakukan hanya setelah kontrak data, kredensial, keamanan, legalitas akses, dan kebutuhan institusi disepakati. Integrasi ini tetap ditunda pada baseline saat ini.

## Aturan kompatibilitas

Fitur baru harus menggunakan instrument version, bukan hard-code nama kriteria atau jumlah kriteria. SPMI tidak boleh diubah agar menyerupai struktur BAN-PT/LAM. Hubungan SPMI ke instrumen eksternal harus melalui mapping versioned.

Evidence tetap link-only sesuai keputusan sebelumnya. File fisik tidak disimpan di aplikasi. Setiap penggunaan tautan pada AMI, RTL, LED, LKPS, atau readiness harus memiliki review dan status validitas yang dapat diaudit.

Status teknis database tetap menggunakan identifier Bahasa Inggris. Seluruh teks yang terlihat pengguna harus menggunakan Bahasa Indonesia.

## Prosedur stabilisasi baseline

1. Jalankan `php artisan optimize:clear`.
2. Jalankan `php artisan migrate:status` dan pastikan tidak ada migration tertunda.
3. Jalankan `php artisan sqm:health`.
4. Jalankan test suite dengan `php artisan test`.
5. Jalankan server menggunakan `php artisan serve`.
6. Jalankan worker menggunakan `php artisan queue:work database --tries=3 --timeout=120`.
7. Login menggunakan akun demo dan uji menu satu per satu.
8. Jika terjadi error, catat URL/menu, waktu, user/role, langkah reproduksi, screenshot, dan 20 baris terakhir log Laravel.
9. Perbaiki satu error sampai tuntas sebelum berpindah ke menu berikutnya.
10. Setelah perbaikan, jalankan kembali lint, test yang relevan, dan smoke test menu terdampak.

## Format laporan error menu

```text
Menu/URL:
Role pengguna:
Tenant/perguruan tinggi/prodi:
Langkah reproduksi:
Hasil yang diharapkan:
Hasil aktual:
Pesan error:
Waktu kejadian:
Potongan storage/logs/laravel.log:
Screenshot:
```

## Kriteria baseline dianggap siap

Baseline dianggap siap diuji lebih lanjut apabila semua migration berhasil, health check berstatus OK, test suite lulus, login dan panel admin dapat dibuka, tenant isolation terverifikasi, menu inti tidak menghasilkan HTTP 500, queue worker dapat memproses job, seeder demo dapat dijalankan ulang secara aman, dan seluruh label UI utama tampil dalam Bahasa Indonesia.

Kesiapan tersebut bukan berarti integrasi eksternal, roll-up institusi, ekspor resmi setiap LAM, backup otomatis, atau production deployment telah selesai. Fitur-fitur tersebut tetap berada pada blueprint tahap berikutnya.

## Dokumen terkait

- `docs/AUDIT_KESIAPAN_SQM_TERHADAP_RANCANGAN_ID.md`
- `docs/ANALISIS_INTEGRASI_SPMI_AMI_AKREDITASI_ID.md`
- `docs/PRA_PRODUKSI_ID.md`
- `docs/ROADMAP_P7_P12_ID.md`
- `docs/CANONICAL_IMPORT_V2_ID.md`
- `docs/RUNTIME_STATUS_QUALIFICATION_ID.md`
