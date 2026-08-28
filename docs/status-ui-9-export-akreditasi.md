# Status UI-9 — Export Laporan Akreditasi PDF dan Excel

## Implementasi

UI-9 menambahkan dependency `maatwebsite/excel` untuk XLSX dan `barryvdh/laravel-dompdf` untuk PDF. Ekspor menggunakan service `AccreditationReportData` sebagai sumber data tunggal agar dashboard dan file laporan tidak memiliki definisi metrik yang berbeda.

`AccreditationReportData` menyediakan filter Perguruan Tinggi, Program Studi, tanggal mulai, dan tanggal akhir. Data yang dihasilkan mencakup kode, judul, scope, prodi, versi instrumen, status, jumlah section, progress LED, progress LKPS, jumlah response, jumlah response selesai, readiness item, readiness selesai, rencana submit, dan hasil keputusan.

## Format ekspor

| Format | Implementasi | Isi |
|---|---|---|
| Excel | `AccreditationReportExport` | XLSX dengan heading, data tabular, auto-size, dan worksheet `Accreditation Report`. |
| PDF | Blade view `reports/accreditation.blade.php` + Dompdf | Tabel ringkas progress akreditasi dengan identitas scope dan timestamp. |

## Halaman reporting

Halaman `AccreditationReport` tersedia pada navigation group `Reporting`. Pengguna dapat memilih Program Studi, periode tanggal, kemudian memilih action `Export Excel` atau `Export PDF`.

Akses report mewajibkan PT aktif pada `TenantContext` dan authorization `viewAny` pada `AccreditationPolicy`. Query program studi pada filter juga hanya mengambil prodi milik PT aktif. `AccreditationPolicy` membatasi akses record berdasarkan PT serta assignment prodi pengguna.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Composer dependency install | Berhasil |
| Composer audit | Tidak ditemukan security advisory |
| Formatter Pint | Lulus |
| Filament | v5.7.6 berhasil boot |
| Test suite | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

PDF/XLSX export telah tersedia pada level aplikasi. Pengujian browser untuk klik download dan inspeksi visual file dapat ditambahkan pada UAT karena memerlukan session panel Filament dan browser.
