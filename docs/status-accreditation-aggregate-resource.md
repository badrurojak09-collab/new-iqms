# Status Langkah 3 — Accreditation Aggregate Resource

## Implementasi

Accreditation Aggregate Resource telah ditambahkan pada navigation group `Accreditation`. Resource mengelola aggregate akreditasi institusi maupun program studi dengan relasi ke Perguruan Tinggi, Program Studi, Instrument Version, dan owner.

Relation Manager yang tersedia:

- Sections.
- Responses.
- Readiness Items.
- Assessments.
- Submissions.
- Decisions.

Query list dibatasi oleh `TenantContext` pada Perguruan Tinggi aktif dan Program Studi aktif. Detail record tetap melewati `AccreditationPolicy`.

## Validasi

- Route aggregate terdaftar pada `admin/accreditations`.
- Composer autoload berhasil.
- Pint berhasil.
- Test suite: 19 test lulus dengan 46 assertions.
- Notification action URL diperbaiki agar tidak gagal ketika object accreditation belum memiliki primary key pada test.

## Catatan backlog

Workflow state transition belum dikunci sepenuhnya sebagai state machine. Action khusus `review`, `approve`, `publish`, dan `submit` perlu ditambahkan setelah aturan bisnis final disepakati. Validasi lintas relasi seperti program studi harus berada pada Perguruan Tinggi yang sama masih perlu diperkuat pada form mutation dan policy.
