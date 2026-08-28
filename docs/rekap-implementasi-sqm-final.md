# Rekap Implementasi Platform SQM

## Ringkasan

Platform Smart Quality Management telah memiliki fondasi backend/domain dan rangkaian UI Filament untuk organisasi multi-tenant Yayasan → Perguruan Tinggi → Program Studi. Implementasi mencakup SPMI, AMI/RTM/RTL, instrument registry, evidence center, akreditasi, dashboard, reporting, notifikasi deadline, RBAC, dan audit log.

**Integrasi API eksternal sengaja ditunda** sesuai keputusan pengguna. Tidak ada integrasi API BAN-PT, LAM, email provider, WhatsApp, atau layanan pihak ketiga yang akan diaktifkan pada paket ini.

## Status berdasarkan fase

| Fase | Status | Hasil |
|---|---|---|
| Backend/domain 1–12 | Selesai secara fondasi | Tenant hierarchy, instrument versioning, evidence hashing, SPMI/PPEPP, AMI/RTM/RTL, accreditation aggregate, migration/UAT/security plan. |
| UI-1 | Selesai | Admin panel Filament, Yayasan, PT, Prodi, User Tenant Scope. |
| UI-2 | Selesai | Dashboard tenant-aware, Quality Overview, global search tenant-aware pada resource organisasi. |
| UI-3/UI-4 | Selesai | Instrument Family, Version, Node, Evidence, upload version privat, SHA-256 workflow. |
| UI-5/UI-6 | Selesai parsial | Scoring Rules, LED Template, LKPS Template, Relation Manager LED sections dan LKPS columns. |
| UI-7 | Selesai | Accreditation Criteria, Instrument Mapping, migration dan model baru. |
| UI-8 | Selesai | Dashboard progress LED/LKPS, response, readiness, mapping coverage. |
| UI-9 | Selesai | Export PDF/XLSX dengan filter PT/prodi/periode dan policy authorization. |
| UI-10 | Selesai baseline | Database notifications, reminder command, daily scheduler, dedupe key, Filament polling. |
| UI-11 | Selesai baseline | Role & permission Resource, User Tenant Scope, audit log, logger, observer assignment scope. |

## File UI/resource utama

| Area | Resource/komponen |
|---|---|
| Organization | `YayasanResource`, `PerguruanTinggiResource`, `ProgramStudiResource`, `UserTenantScopeResource` |
| Instrument Registry | `InstrumentFamilyResource`, `InstrumentVersionResource`, `InstrumentNodeResource`, `InstrumentScoringRuleResource` |
| Accreditation template | `LedTemplateResource`, `LkpsTemplateResource`, `LedTemplateSectionsRelationManager`, `LkpsTemplateColumnsRelationManager` |
| Accreditation mapping | `AccreditationCriterionResource`, `InstrumentMappingResource` |
| Evidence | `EvidenceResource` dengan upload version action |
| Security | `RoleResource`, `AuditLogResource` |
| Dashboard/report | `QualityOverview`, `AccreditationProgress`, `AccreditationReport` |

## Fondasi service dan workflow

Fondasi penting yang ikut dikemas meliputi `TenantContext`, `ScopedRoleManager`, `QualityDashboardMetrics`, `AccreditationReportData`, `StoreEvidenceDocument`, `PublishInstrumentVersion`, `ImportInstrumentVersion`, `CalculateReadiness`, `BuildSubmissionManifest`, `SubmitAccreditation`, `WorkflowTransition`, `AuditLogger`, `AccreditationDeadlineReminder`, dan command `accreditation:deadline-reminders`.

## Database dan integritas

Migration telah tersedia untuk organization tenant, instrument registry, evidence center, SPMI, AMI/RTM/RTL, accreditation aggregate, criteria/mapping, notifications, permission tables, migration control, dan audit logs. Evidence disimpan pada storage privat dengan SHA-256. Versi instrumen published/retired dilindungi immutable guard. Audit log melakukan masking field kredensial sensitif.

## Belum diimplementasikan atau masih parsial

| Item | Status | Tindak lanjut |
|---|---|---|
| Accreditation Resource CRUD lengkap | Belum tersedia sebagai Resource utama | Tambahkan Resource aggregate, sections, responses, submissions, assessments, decisions, dan readiness items. |
| Review/detail Evidence | Parsial | Tambahkan Relation Manager review, integrity check, signed download UI, dan approval workflow. |
| Scoring engine runtime | Parsial | Resource rule sudah ada, tetapi evaluator expression dan kalkulasi skor produksi belum menjadi UI/workflow lengkap. |
| LED/LKPS template validation runtime | Parsial | Struktur template sudah ada, tetapi validator pengisian dan error report belum lengkap. |
| Export visual/browser UAT | Belum lengkap | Tambahkan test download browser, inspeksi PDF/XLSX, dan validasi format pada dataset besar. |
| Audit observer domain luas | Parsial | Saat ini observer konkret mencatat UserTenantScope; perlu diperluas ke accreditation, evidence, version publish, mapping, approval, dan submission. |
| Notification preference UI | Belum tersedia | Tambahkan preferensi threshold, channel, role recipient, dan opt-out per tenant. |
| Queue worker deployment | Belum dikonfigurasi | Scheduler terdaftar, tetapi production perlu cron scheduler dan queue worker supervisor/systemd. |
| RBAC policy hardening | Parsial | Role Resource tersedia; policy `viewAny/create/update/delete` perlu dilengkapi konsisten untuk seluruh domain. |
| Tenant/scope switcher UI | Belum lengkap | TenantContext tersedia, tetapi selector perubahan scope aktif belum menjadi komponen panel penuh. |
| Integrasi API eksternal | **Skipped** | Ditunda: BAN-PT, LAM, email provider, WhatsApp, dan API pihak ketiga. |

## Validasi terakhir

- Filament v5.7.6 berhasil boot.
- Migration fresh testing database berhasil.
- Test suite terakhir: 19 test lulus dengan 46 assertions.
- Pint lulus pada file yang diubah.
- Composer audit tidak menemukan security advisory.
- Scheduler reminder terdaftar setiap hari pukul 07:00.

## Rekomendasi urutan lanjutan

Prioritas berikutnya adalah membuat Accreditation Resource aggregate lengkap, menyelesaikan scope switcher, memperluas audit observer ke seluruh domain kritis, kemudian menambahkan browser UAT dan production queue deployment. Integrasi API tetap dipisahkan sebagai fase tersendiri setelah kontrak data, credential management, rate limit, dan kebijakan sinkronisasi disepakati.
