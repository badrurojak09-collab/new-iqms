# Status UI-7 — Accreditation Criteria dan Instrument Mapping

## Implementasi

Karena schema sebelumnya belum memiliki entitas khusus kriteria akreditasi dan pemetaan instrumen, UI-7 menambahkan migration `2026_08_26_000700_create_accreditation_criteria_mappings.php` dengan dua tabel:

| Tabel | Fungsi |
|---|---|
| `accreditation_criteria` | Menyimpan kriteria berdasarkan versi instrumen, kode, nama, deskripsi, required, metadata, dan urutan. |
| `instrument_mappings` | Memetakan instrument node ke criterion dengan mapping type, target type, target key, dan notes. |

Model Eloquent yang ditambahkan adalah `AccreditationCriterion` dan `InstrumentMapping`.

## Resource Filament

`AccreditationCriterionResource` menyediakan pengelolaan kriteria dengan relasi wajib ke `InstrumentVersion`, metadata KeyValue, status required, dan ordering.

`InstrumentMappingResource` menyediakan pemetaan instrument node ke criterion. Field node dan criterion menggunakan `instrument_version_id` yang sama melalui dynamic options, sehingga administrator hanya dapat memilih pasangan yang kompatibel dalam versi instrumen tersebut. Mapping mendukung target `LED`, `LKPS`, dan `Response`, serta tipe `Primary`, `Supporting`, dan `Derived`.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Migration fresh testing database | Berhasil |
| Formatter Pint | Lulus |
| Filament boot | v5.7.6 berhasil boot |
| Test suite | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

Negative test khusus pasangan mapping lintas versi dapat ditambahkan pada tahap berikutnya bersama test Livewire/browser. Schema sudah menyiapkan unique key untuk mencegah mapping duplikat berdasarkan version, node, criterion, dan target type.
