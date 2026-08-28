# Status UI-10 — Notifikasi dan Pengingat Deadline Akreditasi

## Implementasi

UI-10 menggunakan database notifications Laravel dan scheduler aplikasi. Migration notifications dibuat menggunakan generator Laravel, sementara model `User` mengaktifkan `HasDatabaseNotifications` sehingga notifikasi dapat dibaca oleh Filament.

`AccreditationDeadlineReminder` adalah queued notification yang saat ini menggunakan channel database. Payload mencakup tipe notifikasi, judul, isi, accreditation id, PT id, prodi id, tanggal deadline, sisa hari, tingkat urgensi, dedupe key, dan action URL.

## Reminder schedule

Command `accreditation:deadline-reminders` memeriksa `planned_submission_date` setiap hari pada pukul 07:00. Threshold default adalah 30, 14, 7, 1, 0, dan -1 hari. Command dapat menerima custom threshold melalui opsi `--days`.

Scheduler menggunakan `withoutOverlapping()` agar dua proses reminder tidak berjalan bersamaan. Sebelum mengirim, command mencari `dedupe_key` pada database notifications. Dengan demikian, menjalankan command berulang pada hari dan threshold yang sama tidak membuat notifikasi duplikat.

## Recipient dan tenant scope

Recipient dibatasi pada pengguna PT yang bersangkutan, atau pengguna Yayasan yang tidak terikat ke PT tertentu. Jika accreditation terkait prodi, pengguna juga harus memiliki akses prodi tersebut atau tidak memiliki assignment prodi yang lebih sempit.

Filament database notifications diaktifkan dengan polling 60 detik pada Admin Panel. Jika route edit Accreditation belum tersedia, action URL notification menggunakan fallback `/admin` yang aman.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| PHP GD untuk dependency notification/export ecosystem | Terpasang |
| Scheduler registration | Terdaftar setiap hari pukul 07:00 |
| Command manual | Berhasil, 0 reminder pada fixture kosong |
| Notification payload test | Lulus |
| Test suite | 19 passed, 46 assertions |
| Composer audit | Tidak ditemukan security advisory |
| Pint | Lulus |

Pengiriman email/WhatsApp belum diaktifkan; channel database menjadi baseline aman untuk UI in-app. Channel eksternal dapat ditambahkan setelah kredensial dan kebijakan delivery organisasi tersedia.
