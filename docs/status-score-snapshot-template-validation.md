# Permanent Score Snapshot dan Validasi Template

## Snapshot score

Migration `2026_08_26_001100_create_accreditation_score_snapshots.php` membuat tabel snapshot score permanen. Setiap snapshot menyimpan accreditation, instrument version, calculated user, score, status, snapshot hash, rule results, input response snapshot, dan calculated timestamp.

`AccreditationScoreSnapshot` menolak update dan delete melalui model event. Hash SHA-256 dihitung dari aggregate, versi instrumen, score, hasil rule, dan input response canonicalized.

`RuntimeScoringEngine::scoreAndPersist()` menghitung score dalam transaction dan membuat snapshot baru. Action `Calculate Score` pada halaman edit Accreditation sekarang menggunakan method tersebut.

## Validasi lanjutan

`LedLkpsValidator` sekarang memeriksa section dan node LED terhadap versi instrumen, required LED section terhadap response, required LKPS column terhadap response JSON, tipe numeric, minimum, maksimum, allowed values, serta konsistensi node dan criterion pada InstrumentMapping.

## Validasi teknis

- PHP lint lulus.
- Pint lulus.
- Fresh SQLite migration sampai snapshot table berhasil.
- Test suite: 19 test lulus dengan 46 assertions.

## Catatan

Validasi LKPS mengasumsikan nilai kolom disimpan pada response JSON dengan key yang sama seperti `column_key`. Snapshot bersifat append-only; perubahan data menghasilkan snapshot baru, bukan mengubah snapshot lama.
