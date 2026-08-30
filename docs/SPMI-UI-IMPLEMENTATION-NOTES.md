# Implementasi Penyempurnaan UI SPMI

Tanggal implementasi: 31 Agustus 2026.

## Cakupan

Perubahan mencakup filter tenant pada Resource dan dropdown relasi SPMI, service pengajuan realisasi, action **Ajukan**, **Verifikasi**, dan **Evaluasi Otomatis**, Relation Manager hierarki Framework → Standard → Indicator → Target → Realization → Evaluation → Improvement Program, serta penyelarasan SoftDeletes dan label UI berbahasa Indonesia.

## File utama

| Area | File |
|---|---|
| Tenant query | `app/Support/Tenancy/TenantQuery.php` |
| Submit realisasi | `app/Domain/Spmi/SubmitSpmiRealization.php` |
| Verifikasi/evaluasi | `app/Domain/Spmi/VerifySpmiRealization.php`, `app/Domain/Spmi/EvaluateSpmiRealization.php` |
| Action realisasi | `app/Filament/Resources/SpmiRealizations/Tables/SpmiRealizationsTable.php` |
| Dropdown | Seluruh `app/Filament/Resources/Spmi*/Schemas/*Form.php` |
| Hierarki | `app/Filament/Resources/Spmi*/RelationManagers/*RelationManager.php` |
| Soft delete | `database/migrations/2026_08_31_000500_add_soft_deletes_to_spmi_tables.php` dan seluruh model `app/Models/Spmi*.php` |

## Langkah pengujian lokal

Jalankan perintah berikut dari root proyek Laravel:

```bash
php artisan migrate
php artisan optimize:clear
php artisan test --filter='Spmi|TenantIsolation|IndonesianUi'
```

Migration tambahan bersifat idempoten terhadap kolom `deleted_at`; kolom hanya ditambahkan jika tabel tersedia dan kolom belum ada.

## Alur UAT minimum

1. Masuk sebagai pengguna dengan permission `manage spmi` dan tenant PT/Prodi yang sesuai.
2. Pastikan dropdown Framework, Standard, Indicator, Target, Realization, PT, dan Prodi tidak menampilkan data tenant lain.
3. Buat realisasi dengan nilai numerik atau teks, lalu gunakan **Ajukan**.
4. Verifikasi realisasi menggunakan **Verifikasi** dan isi catatan bila diperlukan.
5. Setelah status menjadi terverifikasi, gunakan **Evaluasi Otomatis** dan isi analisis, akar masalah, serta rekomendasi.
6. Buka data Framework dan pastikan tab **Standar** tampil. Ulangi pemeriksaan hingga tab **Program Peningkatan** pada Evaluation.
7. Uji filter **Data Terhapus**, **Pulihkan**, dan **Hapus Permanen** pada setiap Resource SPMI.
8. Ulangi pemeriksaan sebagai `super_admin`; data lintas tenant hanya boleh terbuka untuk role tersebut sesuai kebijakan aplikasi.

## Catatan kompatibilitas

Action verifikasi menggunakan permission yang sudah tersedia, yaitu `manage spmi`; permission `verify spmi` tidak ditambahkan agar tidak terjadi mismatch dengan `RolePermissionSeeder`. Action Program Peningkatan tetap memakai `verify spmi improvement` sesuai konfigurasi lifecycle yang sudah ada.

## Validasi yang sudah dijalankan

Lint PHP untuk domain, model, Resource SPMI, Relation Manager, helper tenancy, dan migration berhasil. `php artisan optimize:clear` berhasil. Test terarah SPMI, tenant isolation, dan Indonesian UI berhasil pada lingkungan proyek.
