# Runtime Scoring dan Validasi LED/LKPS

## Implementasi

`RuntimeScoringEngine` mengevaluasi rule berdasarkan `instrument_version_id` yang digunakan Accreditation. Rule yang didukung adalah `weighted_sum`, `threshold`, `mapping`, dan formula deklaratif dengan operasi `sum`, `average`, `min`, atau `max`.

`LedLkpsValidator` memeriksa keterhubungan section dan response dengan aggregate, kesesuaian instrument node terhadap versi instrumen, nilai response wajib, serta tipe numeric. Hasil validator mengembalikan status valid, daftar error dengan scope/key/message, dan summary.

Halaman edit Accreditation memiliki action `Validate LED/LKPS` dan `Calculate Score`. Action validation menampilkan error per key, sedangkan scoring menampilkan skor agregat dan jumlah rule yang dievaluasi.

## Proteksi versi

Engine hanya mengambil scoring rules dari versi instrumen milik Accreditation. Validator menolak node yang berasal dari versi berbeda. Snapshot score permanen belum dibuat; hasil saat ini dihitung saat action dijalankan.

## Validasi teknis

- Pint lulus.
- Composer autoload lulus.
- Route Accreditation terdaftar.
- Test suite existing: 19 test lulus dengan 46 assertions.

## Backlog

Snapshot score perlu ditambahkan sebelum assessment final agar histori tidak berubah ketika rule baru dibuat. Validator berikutnya perlu menghubungkan langsung `LedTemplateSection`, `LkpsTemplateColumn`, dan `InstrumentMapping` untuk memvalidasi required/range/enum per template.
