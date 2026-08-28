# Audit Standardisasi Resource Filament SQM

## Keputusan desain

Seluruh form Resource utama menggunakan satu `Section`/card utama, layout dua kolom responsif, spacing bawaan Filament, ikon, deskripsi Bahasa Indonesia, serta field relasi yang dapat dicari. Tabel utama menggunakan kolom informatif, pencarian, sorting, badge status, action Edit, action Hapus, dan aksi massal jika model mendukung soft delete.

Model, class, method, field database, dan identifier teknis tetap menggunakan penamaan asli. Bahasa Indonesia diterapkan pada navigation label, judul halaman, label field, helper text, status, notifikasi, action, dan judul kolom.

## Resource utama yang tersedia

| Domain | Resource |
|---|---|
| Organisasi | Yayasan, Perguruan Tinggi, Program Studi, User Tenant Scope |
| SPMI | Framework, Standar, Indikator, Target, Realisasi, Evaluasi, Program Peningkatan |
| Instrument | Family, Version, Node, Criterion, Element, Indicator, Scale, Rubric, Threshold, Scoring Rule, Mapping |
| Evidence | Evidence Collection, Evidence |
| Akreditasi | Accreditation, Accreditation Criterion |
| Readiness | Readiness Run |
| Governance | RTL Action, Role, Audit Log |
| Template | LED Template, LKPS Template |

## Model detail yang tidak dijadikan menu utama

Beberapa model tidak dibuat sebagai menu mandiri karena merupakan child/detail/pivot atau histori immutable. Model tersebut lebih tepat diakses melalui Relation Manager pada parent masing-masing agar sidebar tidak penuh dan konteks data tetap terjaga. Contohnya adalah `LedTemplateSection`, `LkpsTemplateColumn`, `EvidenceCollectionItem`, `EvidenceVersion`, `EvidenceReview`, `EvidenceLinkCheck`, `AccreditationResponse`, `AccreditationAssessment`, `AccreditationSection`, `AccreditationScoreSnapshot`, `ReadinessResult`, `ReadinessGap`, `RtmParticipant`, dan `RtmDecision`.

Model seperti `MigrationRun`, `MigrationLedger`, `MigrationException`, `InstrumentImportBatch`, dan `InstrumentImportRow` juga merupakan audit/import detail. Model tersebut sebaiknya tampil sebagai read-only monitoring atau relation manager, bukan sebagai menu CRUD umum.

## Resource SPMI yang ditambahkan

Resource baru untuk rantai SPMI berada pada:

- `SpmiFrameworks`
- `SpmiStandards`
- `SpmiIndicators`
- `SpmiTargets`
- `SpmiRealizations`
- `SpmiEvaluations`

Seluruhnya sudah memakai grup navigasi `SPMI` dengan urutan Framework → Standar → Indikator → Target → Realisasi → Evaluasi → Program Peningkatan.

## Catatan validasi

Pada saat standardisasi ditemukan kontrak Filament 5 untuk `$navigationGroup` harus menggunakan `string|UnitEnum|null`, bukan `?string`. Semua Resource SPMI telah disesuaikan dengan kontrak tersebut.

Callback dinamis `options()` tidak menggunakan type-hint `Filament\Forms\Get` karena pada schema Filament 5 utilitas yang diberikan berasal dari namespace Schema. Pola yang dipakai adalah `fn ($get): array`.

## Sisa pekerjaan terkontrol

Resource yang belum memiliki menu mandiri tetapi dapat ditambahkan kemudian sebagai monitoring/read-only adalah snapshot scoring, hasil readiness, gap readiness, batch import, audit migration, review evidence, dan detail submission. Keputusan ini dibuat agar data detail tidak dapat diubah sembarangan dan tetap mengikuti lifecycle parent.

Label eksplisit pada beberapa resource lama seperti `Criterion`, `Object`, `Permissions`, `Calculate Score`, dan `Readiness Runs` masih perlu batch lokalisasi lanjutan. Hal tersebut tidak mengubah identifier atau fungsi runtime.
