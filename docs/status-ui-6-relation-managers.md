# Status UI-6 — Relation Manager LED dan LKPS

## Implementasi

UI-6 menambahkan dua Relation Manager Filament 5 pada halaman edit template:

| Parent Resource | Relation Manager | Fungsi |
|---|---|---|
| `LedTemplateResource` | `LedTemplateSectionsRelationManager` | Mengelola section LED, node instrumen, guidance, validation rules, required, dan urutan. |
| `LkpsTemplateResource` | `LkpsTemplateColumnsRelationManager` | Mengelola kolom LKPS, tipe data, unit, batas nilai, skala desimal, allowed values, formula, required, dan urutan. |

## LED section guard

Pilihan `instrument_node_id` pada LED section hanya mengambil node yang mempunyai `instrument_version_id` sama dengan versi instrumen milik template. Guard kedua diterapkan sebelum pembuatan record melalui `mutateFormDataBeforeCreate`, sehingga node dari versi instrumen lain tidak dapat dipasang ke template secara tidak sengaja.

Section dapat diurutkan melalui `sort_order` dan table reorderable. Validation rules disediakan melalui komponen `KeyValue`.

## LKPS column configuration

LKPS column manager menyediakan konfigurasi struktur data yang dibutuhkan untuk tabel LKPS: `string`, `integer`, `decimal`, `date`, `boolean`, dan `enum`; unit; required; min/max value; decimal scale; source type; allowed values; formula; dan sort order.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Formatter Pint | Lulus |
| Filament boot | v5.7.6 berhasil boot |
| Existing test suite | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

Catatan: validation lintas versi sudah dipasang pada create LED section. Pengujian feature khusus Relation Manager dapat ditambahkan bersamaan dengan pengujian browser/UAT Filament pada fase hardening berikutnya.
