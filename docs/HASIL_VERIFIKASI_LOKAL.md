# Hasil Verifikasi Lokal SQM

Tanggal verifikasi: 26 Agustus 2026.

## Ringkasan

Verifikasi lokal berhasil untuk dependency PHP, dependency frontend, build asset Vite, migration database, health check, seeder data dummy, import manifest legacy, dry-run legacy, boot aplikasi, route listing, database queue worker, dan regression test.

## Hasil command

| Pemeriksaan | Hasil |
|---|---|
| PHP | PHP 8.3.6 terdeteksi |
| Composer | 2.7.1 terdeteksi |
| Node.js | v22.13.0 terdeteksi |
| npm | 10.9.2 terdeteksi |
| Composer install | Berhasil |
| npm install | Berhasil |
| npm run build | Berhasil |
| Migration | Tidak ada migration tertunda |
| `php artisan sqm:health` | Semua pemeriksaan OK |
| Seeder demo | Berhasil |
| Legacy dry-run | Berhasil, tidak menulis target |
| Legacy import | Berhasil; baris baru, duplikat, dan exception terdeteksi sesuai desain |
| Queue worker `--once` | Berhasil terhubung ke antrean default |
| `route:list` | Berhasil |
| `about` | Berhasil |
| `php artisan test` | 22 test passed, 53 assertions |

## Data dummy

Seeder `Database\\Seeders\\SqmDemoSeeder` membuat Yayasan, dua Perguruan Tinggi, Program Studi, user admin, user pengelola mutu, framework SPMI, standar, indikator, target, realisasi, evaluasi, program peningkatan, badan akreditasi BAN-PT, instrument family, instrument version, akreditasi institusi, serta evidence berbasis tautan Google Drive demo.

## Perintah menjalankan ulang

```bash
composer install --no-interaction --prefer-dist
npm install --no-audit --no-fund
npm run build
php artisan migrate
php artisan sqm:health
php artisan db:seed --class=Database\\Seeders\\SqmDemoSeeder --force
php artisan test
```

Untuk aplikasi dan worker:

```bash
php artisan serve
php artisan queue:work database --tries=3 --timeout=120
```

## Catatan

`APP_DEBUG=false` digunakan pada environment lokal project. Build frontend menampilkan catatan opsional dari plugin font mengenai paket `fontaine`; proses build tetap berhasil. Seeder demo harus dipanggil eksplisit dan tidak dipanggil oleh `DatabaseSeeder` default agar data dummy tidak masuk otomatis ke staging atau production.
