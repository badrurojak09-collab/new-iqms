# Format Import Canonical v2

Importer `ImportCanonicalInstrument` sekarang menerima berkas CSV, XLSX, dan JSON. Format lama yang hanya berisi `node`, `criterion`, `element`, dan `indicator` tetap didukung. Format v2 menambahkan `scale`, `scale_option`, `rubric`, `threshold`, dan `qualification_rule`.

## Aturan umum

Setiap baris harus memiliki `entity_type` dan `code`. Field `title` wajib untuk node, criterion, element, indicator, scale, threshold, dan qualification rule dapat menggunakan `label` sebagai pengganti title. Relasi antarbari ditentukan dengan kode, bukan ID database.

| entity_type | Relasi utama | Penyimpanan |
|---|---|---|
| `node` | `parent_code` | `instrument_nodes` |
| `criterion` | `node_code` | `assessment_criteria` |
| `element` | `criterion_code`, `node_code` | `assessment_elements` |
| `indicator` | `element_code` | `assessment_indicators` |
| `scale` | Tidak ada | `assessment_scales` |
| `scale_option` | `scale_code` | `assessment_scale_options` |
| `rubric` | `node_code`, `scale_option_code` opsional | `assessment_rubrics` |
| `threshold` | `indicator_code` atau `element_code` | `assessment_thresholds` |
| `qualification_rule` | Tidak ada | `instrument_scoring_rules` dan metadata `instrument_versions.changelog` |

## Kolom canonical-v2

```text
entity_type,code,title,node_type,parent_code,node_code,criterion_code,element_code,
indicator_code,scale_code,scale_option_code,rule_type,label,description,
evidence_expectation,dimension,unit,data_type,direction,weight,min_score,max_score,
numeric_value,comparison,target_value,min_value,max_value,pass_score,fail_score,
minimum_score,aggregation_key,aggregation_operator,aggregation_min_passed,sequence,
status,source_reference,requirement,guidance,is_required,sort_order,expression,
parameters,config,metadata,target_definition
```

## Descriptor skor

Gunakan `scale` untuk mendefinisikan skala dan `scale_option` untuk setiap nilai. Descriptor rinci dapat disimpan pada `rubric.description`, sedangkan harapan bukti dapat disimpan pada `rubric.evidence_expectation`.

Contoh JSON:

```json
{
  "entity_type": "rubric",
  "code": "LAM21-C1-4",
  "title": "Sangat Baik",
  "node_code": "C1",
  "scale_option_code": "SCORE-4",
  "min_score": 4,
  "max_score": 4,
  "description": "Kebijakan tersedia lengkap dan dilaksanakan sangat efektif.",
  "evidence_expectation": "Dokumen sahih dan sangat lengkap.",
  "source_reference": "Sarjana-MatriksPenilaian.pdf halaman 1"
}
```

## Threshold bertingkat

Setiap tingkat threshold dibuat sebagai satu baris `threshold`. Gunakan `sequence` untuk urutan evaluasi, `comparison` dengan nilai `gte`, `lte`, `eq`, atau `between`, dan `min_value`/`max_value` untuk interval. `pass_score` adalah skor yang diberikan ketika kondisi terpenuhi.

Contoh rasio dosen-mahasiswa:

```json
[
  {
    "entity_type": "threshold",
    "code": "LAM21-RASIO-4",
    "title": "Rasio dosen mahasiswa skor 4",
    "indicator_code": "LAM21-IND-RASIO",
    "comparison": "lte",
    "max_value": 60,
    "pass_score": 4,
    "direction": "lower_is_better",
    "aggregation_key": "Relevansi Pendidikan",
    "aggregation_operator": "all",
    "sequence": 1,
    "status": "draft",
    "source_reference": "INSTRU_1.PDF halaman 1"
  },
  {
    "entity_type": "threshold",
    "code": "LAM21-RASIO-3",
    "title": "Rasio dosen mahasiswa skor 3",
    "indicator_code": "LAM21-IND-RASIO",
    "comparison": "between",
    "min_value": 61,
    "max_value": 70,
    "pass_score": 3,
    "direction": "lower_is_better",
    "aggregation_key": "Relevansi Pendidikan",
    "aggregation_operator": "all",
    "sequence": 2,
    "status": "draft",
    "source_reference": "INSTRU_1.PDF halaman 1"
  }
]
```

## Rule Unggul LAM INFOKOM 2.1

Gunakan `qualification_rule` untuk rule agregasi status. `expression` menyimpan kondisi hard gate, sedangkan `parameters` menyimpan masa berlaku dan parameter tambahan. Importer menyimpan rule tersebut sebagai `InstrumentScoringRule` bertipe `status_qualification` serta meringkasnya dalam `instrument_versions.changelog.qualification_rules`.

Contoh:

```json
{
  "entity_type": "qualification_rule",
  "code": "LAM21-UNGGUL-3",
  "title": "Terakreditasi Unggul berlaku tiga tahun",
  "rule_type": "status_qualification",
  "source_reference": "KriteriaUnggul2.1LAMINFOKOM.pdf",
  "expression": {
    "all": [
      {"metric": "final_score", "gte": 321},
      {"metric": "Budaya Mutu.item_min", "gte": 3},
      {"metric": "Relevansi Pendidikan.item_min", "gte": 3},
      {"metric": "Relevansi Penelitian.item_min", "gte": 3}
    ]
  },
  "parameters": {
    "min_average": 3.2,
    "validity_years": 3
  }
}
```

## Workflow yang disarankan

Gunakan menu import untuk melakukan preview terlebih dahulu. Review jumlah row dan error. Pastikan total bobot matriks mencapai nilai yang ditetapkan dokumen. Pastikan semua foreign reference seperti `criterion_code`, `element_code`, `indicator_code`, dan `scale_code` ditemukan. Setelah itu commit sebagai versi `draft`, lakukan review dan approval, lalu publish untuk menghasilkan content hash immutable.

Rule yang statusnya `draft` atau `pending_review` tidak boleh dipakai sebagai rule aktif oleh runtime scoring. Setelah disetujui dan versi instrumen dipublikasikan, perubahan harus dibuat sebagai instrument version baru.

## Catatan validasi

Batas interval `>` versus `>=`, pembulatan rasio, dan nilai yang berada tepat pada batas harus divalidasi terhadap tabel PDF asli sebelum dipublikasikan. `source_reference` sebaiknya selalu mencantumkan nama dokumen dan nomor halaman agar auditor dapat menelusuri dasar aturan.
