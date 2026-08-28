# Status UI-11 — RBAC dan Audit Log Aktivitas

## RBAC

UI-11 menambahkan `RoleResource` pada navigation group `Security`. Role dapat dibuat dan diperbarui melalui Filament, dengan assignment permission menggunakan `CheckboxList` melalui relasi Spatie Permission.

Resource `UserTenantScope` yang sudah tersedia tetap menjadi mekanisme assignment role per scope Yayasan, PT, atau Prodi. Role permission yang telah disiapkan pada `RolePermissionSeeder` dapat dikelola dari UI role.

## Audit log

Migration `2026_08_26_000800_create_audit_logs.php` dan model `AuditLog` ditambahkan untuk menyimpan user, event, object yang berubah, route, IP address, user agent, old values, new values, dan context.

`AuditLogger` menjadi service terpusat untuk pencatatan activity dan melakukan masking terhadap password, password confirmation, remember token, serta API token.

`UserTenantScopeObserver` mencatat event `scope.created`, `scope.updated`, dan `scope.deleted`, sehingga perubahan assignment scope dan role dapat dilacak. `AuditLogResource` menyediakan viewer read-only dengan pengurutan waktu terbaru, user, event, object, IP, dan route.

## Authorization dan keamanan

Audit log tidak menyediakan create atau delete melalui UI. Detail perubahan dibuka dalam mode disabled. `AccreditationPolicy` tetap digunakan untuk akses akreditasi dan pola permission Spatie tetap menjadi dasar akses role.

## Validasi

| Pemeriksaan | Hasil |
|---|---|
| Migration fresh testing database | Berhasil |
| Pint | Lulus |
| Filament | v5.7.6 berhasil boot |
| Test suite | 19 passed, 46 assertions |
| Composer audit | Tidak ditemukan security advisory |
| Cache/config/routes/views clear | Berhasil |

Audit logging untuk domain model lain dapat diperluas dengan observer yang sama pada tahap security hardening, terutama untuk perubahan accreditation, evidence, instrument version, dan approval.
