# Aturan Coding Aider untuk new-qms

## Wajib sebelum coding

1. Baca `AI-CODING-AGENT-CONTEXT-BASELINE.md`.
2. Baca migration, model, policy, service, dan Resource yang akan diubah.
3. Periksa tenant boundary dan permission.
4. Periksa apakah lifecycle service sudah tersedia.
5. Jangan mengubah versi instrumen aktif atau score snapshot lama.

## Aturan tenant

- Jangan bypass `TenantContext` atau `ResolveTenantContext`.
- Semua query operasional harus tenant-aware.
- Semua Resource, relation manager, export, report, job, dan global search harus diperiksa terhadap cross-tenant leakage.
- Impersonation hanya boleh digunakan oleh `super_admin`.

## Aturan database

- Jangan menggunakan kolom yang tidak tersedia pada migration.
- Jangan menggunakan `code` pada `assessment_rubrics`; schema aktual tidak memiliki kolom tersebut.
- Gunakan nama index dan foreign key pendek agar aman terhadap batas identifier MySQL.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, atau penghapusan data tanpa persetujuan.

## Aturan instrumen dan scoring

- Instrument version yang aktif bersifat immutable.
- Perubahan rubric, threshold, atau scoring rule harus melalui draft, review, approval, dan publish.
- Snapshot historis tidak boleh dihitung ulang atau diubah.
- Jangan menanamkan formula LAM/BAN-PT di controller.
- Tambahkan test untuk threshold boundary, direction, aggregation, rubric range, dan status qualification.

## Aturan evidence

- Evidence menggunakan link-only Google Drive/cloud.
- Jangan menambahkan upload file fisik sebagai default.
- Link check history dan evidence review harus dipertahankan.

## Aturan UI

- Semua label dan keterangan UI harus Bahasa Indonesia.
- Form menggunakan layout card yang rapi.
- Dropdown relasi harus menampilkan kode/nama bisnis, bukan ID mentah saja.
- Group menu utama memakai icon; submenu tidak perlu icon individual.

## Aturan verifikasi

Setelah perubahan jalankan minimal:

```bash
php artisan optimize:clear
php artisan test
```
