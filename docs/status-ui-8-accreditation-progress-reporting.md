# Status UI-8 — Dashboard Progress dan Reporting Akreditasi

## Implementasi

Service `QualityDashboardMetrics` diperluas untuk menyediakan metrik reporting akreditasi per Perguruan Tinggi:

| Metrik | Sumber |
|---|---|
| Progress LED | Rata-rata `readiness_percent` pada section bertipe `led` |
| Progress LKPS | Rata-rata `readiness_percent` pada section bertipe `lkps` |
| Response completion | Persentase response dengan status submitted, verified, atau approved |
| Readiness item rate | Persentase readiness item dengan status done, completed, atau verified |
| Mapping count | Jumlah mapping instrumen dari versi yang digunakan oleh akreditasi PT |
| Accreditation ready rate | Agregasi status readiness pada accreditation aggregate |

Widget baru `AccreditationProgress` telah didaftarkan pada Admin Panel. Widget menampilkan progress LED, LKPS, response completion, readiness item rate, jumlah section, dan jumlah mapping instrumen. Widget membaca `TenantContext`; tanpa PT aktif, widget tidak menjalankan agregasi lintas tenant.

Widget `QualityOverview` yang sudah ada tetap menampilkan metrik SPMI, AMI, RTL, dan readiness akreditasi sehingga dashboard sekarang menggabungkan operational quality monitoring dengan accreditation progress monitoring.

## Tenant isolation

Semua metrik akreditasi menggunakan `perguruan_tinggi_id` dari tenant aktif. Query turunan untuk section, response, readiness item, dan accreditation dibatasi menggunakan `accreditation_id` yang berasal dari PT tersebut. Mapping hanya dihitung dari `instrument_version_id` yang digunakan oleh aggregate akreditasi PT.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Formatter Pint | Lulus |
| Filament | v5.7.6 berhasil boot |
| Test suite | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

Reporting lanjutan seperti export Excel/PDF, drill-down per akreditasi, filter rentang tanggal, dan grafik historis dapat ditambahkan pada subfase reporting berikutnya tanpa mengubah kontrak service metrik yang sudah diperluas.
