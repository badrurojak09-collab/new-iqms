# Status UI-4 — Instrument Registry dan Evidence Center

## Implementasi

UI-4 menambahkan Resource Filament untuk `InstrumentFamily`, `InstrumentVersion`, `InstrumentNode`, dan `Evidence`.

Instrument Registry kini menyediakan katalog family instrumen, versi instrumen, serta node hierarkis seperti standar, kriteria, elemen, dan indikator. Field versi mencakup status, parent version, referensi sumber, periode efektif, changelog, dan content hash. Resource menghormati concern `ImmutableInstrumentVersion`: versi berstatus `published` atau `retired` tidak dapat diedit atau dihapus melalui Resource.

Evidence Center menyediakan metadata evidence berdasarkan PT dan prodi, status evidence, masa berlaku, daftar versi, serta action `Upload Versi`. Action upload tidak menghitung hash atau menulis metadata dokumen secara manual. Proses tersebut didelegasikan ke `StoreEvidenceDocument`, yang sudah menangani validasi file, SHA-256, private local storage, penamaan path berbasis tenant/evidence/version/hash, pembuatan `documents`, dan pembuatan `evidence_versions`.

## Tenant isolation

`EvidenceResource::getEloquentQuery()` menolak query tanpa authenticated user, memberi akses penuh hanya untuk `super_admin`, dan membatasi pengguna biasa ke PT aktifnya. Jika pengguna memiliki assignment prodi, evidence juga dibatasi ke prodi yang ditugaskan. Policy `EvidencePolicy` tetap menjadi lapisan authorization pada operasi Resource.

## Resource map

| Resource | Fungsi | Guard utama |
|---|---|---|
| `InstrumentFamilyResource` | Katalog family instrumen | Access panel/policy |
| `InstrumentVersionResource` | Versioning instrumen | `isImmutable()` pada published/retired |
| `InstrumentNodeResource` | Hierarki node instrumen | Relasi version dan parent |
| `EvidenceResource` | Metadata evidence dan upload versi | Tenant-aware Eloquent query, policy, private storage service |

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Formatter Pint | Lulus |
| Filament | v5.7.6 berhasil boot |
| Test suite | 18 passed, 41 assertions |
| Cache/config/routes/views clear | Berhasil |

## Batasan UI-4

Resource `Document` terpisah belum dibuat karena dokumen harus dikelola sebagai bagian dari Evidence Center melalui workflow `StoreEvidenceDocument`; pendekatan ini mencegah admin membuat row dokumen tanpa file privat dan tanpa hash integrity. Resource scoring rules, LED template, dan LKPS template disiapkan untuk fase UI berikutnya.
