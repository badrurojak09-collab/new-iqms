# ScoreSnapshots Read-only Relation Manager

Relation Manager `AccreditationScoreSnapshotsRelationManager` telah didaftarkan pada `AccreditationResource` melalui relasi `scoreSnapshots`.

Tabel menampilkan waktu kalkulasi, score, status, versi instrumen, user penghitung, dan sebagian snapshot hash. Hash dapat disalin dari tabel. Action `View Snapshot` menampilkan score, status, hash, waktu kalkulasi, rule results, dan input snapshot dalam modal disabled.

Tidak tersedia action create, edit, delete, bulk action, atau header action. Model `AccreditationScoreSnapshot` juga menolak update dan delete melalui model event sehingga read-only berlaku pada UI dan model layer.

Validasi: Pint lulus, PHP lint lulus, Composer autoload berhasil, route Accreditation berhasil dimuat, dan 19 test lulus dengan 46 assertions.
