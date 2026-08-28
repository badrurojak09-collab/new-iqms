# Status UI-5 — Scoring Rules dan Template LED/LKPS

## Implementasi selesai

UI-5 menambahkan model dan Resource Filament untuk:

| Komponen | Resource | Isi utama |
|---|---|---|
| Scoring | `InstrumentScoringRuleResource` | Instrument version, code, rule type, expression, parameters |
| LED | `LedTemplateResource` | Instrument version, code, name, description, validation rules |
| LKPS | `LkpsTemplateResource` | Instrument version, code, name, row definition, validation rules, required, sort order |

Model `InstrumentScoringRule` dibuat karena tabel `instrument_scoring_rules` sebelumnya belum memiliki model Eloquent.

## Editor dan validasi

Field JSON menggunakan komponen `KeyValue` Filament sehingga administrator dapat menyusun expression, parameters, row definition, dan validation rules tanpa menulis raw JSON secara langsung. Semua template dan scoring rule wajib terhubung ke `InstrumentVersion`, sehingga perubahan instrumen tetap terisolasi melalui versi registry.

Resource ditampilkan dalam navigation group `Instrument Registry` dan `Accreditation Templates`. Table menyediakan pencarian kode/nama, relasi family/version, status metadata, jumlah section/column, serta ordering.

## Validasi teknis

| Pemeriksaan | Hasil |
|---|---|
| Formatter Pint | Lulus |
| Autoload model/resource | Lulus |
| Filament | v5.7.6 berhasil boot |
| Test suite existing | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

## Batasan yang disiapkan untuk subfase berikutnya

Resource parent LED dan LKPS sudah tersedia. Pengelolaan detail `LedTemplateSection` dan `LkpsTemplateColumn` belum dibuat sebagai Relation Manager pada UI-5 ini. Keduanya sebaiknya ditambahkan berikutnya agar administrator dapat mengelola section LED dan kolom LKPS langsung dari halaman template, dengan guard bahwa node/kolom harus berasal dari `InstrumentVersion` yang sama.
