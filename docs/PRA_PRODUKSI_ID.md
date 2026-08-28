# Checklist Pra-Produksi SQM

Dokumen ini digunakan untuk verifikasi lokal sebelum sistem dipindahkan ke lingkungan staging atau produksi.

## 1. Persiapan aplikasi

Pastikan PHP 8.3 atau lebih baru, Composer, MySQL 8.4, dan Node.js tersedia. Salin `.env.example` menjadi `.env`, isi `APP_KEY`, kredensial database, serta pastikan `APP_LOCALE=id`, `APP_FALLBACK_LOCALE=id`, `APP_TIMEZONE=Asia/Jakarta`, dan `QUEUE_CONNECTION=database`.

Jalankan perintah berikut dari direktori project:

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan optimize:clear
npm install
npm run build
```

## 2. Proses background

Jalankan worker queue pada terminal terpisah:

```bash
php artisan queue:work database --tries=3 --timeout=120
```

Setelah program peningkatan SPMI diubah dari **Selesai** menjadi **Terverifikasi**, pastikan kolom proses readiness berpindah dari **Dalam Antrean** ke **Sedang Diproses**, lalu menjadi **Selesai**. Untuk pemeriksaan kegagalan gunakan `php artisan queue:failed`.

## 3. Smoke test modul inti

Masuk ke `/admin` menggunakan akun yang memiliki scope tenant. Pastikan menu organisasi, pengguna, instrumen, standar SPMI, evaluasi AMI, evidence berbasis tautan cloud, akreditasi, readiness, RTL, dan laporan dapat dibuka tanpa error 500.

Uji isolasi tenant dengan memilih dua Perguruan Tinggi berbeda. Data tenant pertama tidak boleh tampil pada tenant kedua. Uji pula pembatasan Program Studi agar pengguna hanya dapat melihat scope yang ditugaskan.

## 4. Alur bisnis minimum

Buat standar SPMI, indikator, target, dan realisasi. Verifikasi realisasi, jalankan evaluasi, buat program peningkatan, lalu lakukan transisi **Direncanakan → Sedang Berjalan → Selesai → Terverifikasi**. Pastikan verifikasi program yang memiliki accreditation terkait mengantrikan readiness re-evaluation.

Buat versi instrumen, kriteria, elemen, indikator, skala, rubrik, serta threshold. Uji bahwa versi yang sudah diterbitkan bersifat immutable dan threshold/rubrik harus melalui persetujuan sebelum digunakan.

Buat koleksi evidence dan item persyaratan menggunakan URL Google Drive atau penyimpanan cloud institusi. Pastikan sistem tidak membutuhkan upload berkas lokal. Jalankan pemeriksaan tautan dan lihat riwayat pemeriksaannya.

## 5. Validasi otomatis

Jalankan seluruh test suite:

```bash
php artisan test
```

Test suite harus selesai tanpa kegagalan. Pada baseline saat dokumen ini dibuat, seluruh **21 test dengan 52 assertions** berhasil.

## 6. Pemeriksaan sebelum staging

Sebelum staging, aktifkan `APP_DEBUG=false`, gunakan kredensial database khusus aplikasi dengan hak minimum, siapkan backup MySQL terjadwal, konfigurasi worker sebagai service supervisor atau systemd, dan pastikan `storage` serta `bootstrap/cache` dapat ditulis oleh user aplikasi. Integrasi API eksternal tetap belum termasuk dalam baseline ini.
