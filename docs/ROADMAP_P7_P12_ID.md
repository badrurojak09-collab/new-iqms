# Status Implementasi P7-P12 SQM

## P7 — Import, migrasi, dan dual-run

Baseline import manifest sudah tersedia melalui `php artisan legacy:migrate-import <file.csv|file.json>`. Command ini menghitung checksum sumber, membuat `migration_runs`, mencatat baris valid pada `migration_ledgers`, mencatat baris invalid pada `migration_exceptions`, bersifat idempotent, dan tidak menulis data target secara langsung. Mode dry-run tersedia melalui `legacy:migrate-dry-run`.

Format CSV minimum adalah `source_table,source_pk,target_table,target_id,status,message`. Import ini menjadi lapisan aman untuk review mapping sebelum worker migrasi domain dibuat.

## P8 — Security dan tenant hardening

Tenant context memvalidasi akses Perguruan Tinggi dan Program Studi terhadap user scope. Middleware sekarang membersihkan context pada blok `finally`, sehingga context tidak tertinggal ketika request gagal atau aplikasi berjalan dengan worker berumur panjang. Regression test tenant isolation sudah tersedia.

## P9 — Testing dan UAT

Test suite Laravel mencakup aggregate akreditasi, submission, notifikasi deadline, AMI/RTM/RTL, integrasi lintas domain, evidence, immutable instrument, SPMI/PPEPP, global search, tenant isolation, locale UI, dan dispatch queue. Skenario UAT minimum tersedia pada `docs/PRA_PRODUKSI_ID.md`.

## P10 — Performance dan observability

Readiness re-evaluation menggunakan database queue dengan retry dan backoff. Command `php artisan sqm:health` memeriksa locale, timezone, koneksi database, tabel queue, tabel tenant, dan cache. Monitoring lanjutan dengan Supervisor, Horizon, atau layanan observability eksternal tetap diperlukan pada staging/production.

## P11 — Backup, disaster recovery, dan staging

Sebelum staging, lakukan backup MySQL menggunakan `mysqldump`, simpan backup di lokasi terpisah, uji restore pada database terisolasi, dan verifikasi `storage` serta `bootstrap/cache`. Evidence tidak dibackup sebagai file lokal karena kebijakan link-only; yang dibackup adalah metadata, URL cloud, checksum metadata, dan riwayat pemeriksaan tautan.

## P12 — Pilot dan go-live

Pilot dimulai dari satu Yayasan, satu Perguruan Tinggi, dan satu Program Studi menggunakan scope terbatas. Setelah UAT disetujui, lakukan cutover bertahap, freeze perubahan instrumen, backup sebelum cutover, pantau queue dan log, serta siapkan rollback ke sistem legacy. Integrasi API eksternal masih ditunda.

## Data dummy

Seeder dummy tidak dipanggil oleh `DatabaseSeeder` default agar tidak pernah mengotori staging atau production. Jalankan secara eksplisit:

```bash
php artisan db:seed --class=Database\\Seeders\\SqmDemoSeeder --force
```

Akun demo yang dibuat adalah `admin.demo@sqm.test` dan `mutu.demo@sqm.test`, dengan password `password`. Ubah password tersebut segera setelah login pada lingkungan lokal.

## Pemeriksaan lokal

```bash
php artisan migrate
php artisan sqm:health
php artisan test
php artisan serve
php artisan queue:work database --tries=3 --timeout=120
```
