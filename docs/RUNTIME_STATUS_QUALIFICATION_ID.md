# Implementasi Runtime Evaluator `status_qualification`

## Alur eksekusi

Runtime evaluator menjalankan proses berikut:

```text
Ambil scoring rule versi instrumen
        │
        ├── scoring rule biasa
        │      └── weighted_sum / threshold / mapping / formula
        │
        ├── canonical threshold approved
        │
        └── status_qualification
                 └── evaluasi setelah skor dan metrik kelompok tersedia

Hitung skor akhir
        │
Bangun context: final_score, item_min, item_average
        │
Evaluasi expression all/any dan operator per kondisi
        │
Pilih qualification rule dengan masa berlaku tertinggi yang lulus
        │
Simpan status dan detail ke score snapshot immutable
```

## 1. Memisahkan scoring rule dan qualification rule

Di dalam `score()` pada `app/Domain/Accreditation/RuntimeScoringEngine.php`:

```php
$scoringRules = $rules->reject(
    fn (InstrumentScoringRule $rule): bool =>
        $rule->rule_type === 'status_qualification'
);

$qualificationRules = $rules->filter(
    fn (InstrumentScoringRule $rule): bool =>
        $rule->rule_type === 'status_qualification'
);

$evaluated = $scoringRules
    ->map(fn (InstrumentScoringRule $rule): array =>
        $this->evaluate($rule, $values)
    )
    ->values()
    ->all();
```

Pemisahan ini penting karena `status_qualification` tidak menghasilkan skor indikator. Rule tersebut menghasilkan status akhir, misalnya `unggul_3_tahun` atau `unggul_5_tahun`.

## 2. Mengevaluasi threshold canonical yang disetujui

Hanya threshold berstatus `approved` yang digunakan oleh runtime:

```php
$canonicalThresholds = AssessmentThreshold::query()
    ->with(['element', 'indicator', 'rubric', 'scale'])
    ->where('instrument_version_id', $versionId)
    ->where('status', 'approved')
    ->get();

foreach ($canonicalThresholds as $threshold) {
    $field = $threshold->indicator?->code
        ?? $threshold->element?->code
        ?? $threshold->code;

    $evaluated[] = $this->evaluateCanonicalThreshold(
        $threshold,
        $field,
        $values[$field] ?? null,
    );
}
```

Hasil threshold memuat `score`, `passed`, `weight`, dan `aggregation_key`. `aggregation_key` digunakan untuk mengelompokkan nilai kriteria seperti Budaya Mutu, Relevansi Pendidikan, dan Relevansi Penelitian.

## 3. Menghitung skor akhir dan memanggil qualification evaluator

```php
$scores = collect($evaluated)
    ->pluck('score')
    ->filter(fn ($score) => $score !== null)
    ->map(fn ($score): float => (float) $score);

$score = $scores->isEmpty()
    ? 0.0
    : round((float) $scores->avg(), 4);

$qualification = $this->evaluateQualifications(
    $qualificationRules->values()->all(),
    $score,
    $evaluated,
);

$evaluated[] = [
    'rule_type' => 'qualification_summary',
    'status' => $qualification['status'],
    'passed' => $qualification['passed'],
    'validity_years' => $qualification['validity_years'],
    'failed_rules' => $qualification['failed_rules'],
];
```

Hasil `score()` sekarang memiliki bentuk berikut:

```php
[
    'instrument_version_id' => 12,
    'score' => 347.5,
    'status' => 'unggul_3_tahun',
    'qualification' => [
        'passed' => true,
        'status' => 'unggul_3_tahun',
        'qualification_rule' => 'LAM21-UNGGUL-3',
        'validity_years' => 3,
        'failed_rules' => [],
        'context' => [
            'final_score' => 347.5,
            'Budaya Mutu.item_min' => 3,
            'Budaya Mutu.item_average' => 3.4,
        ],
    ],
    'rules' => [],
]
```

## 4. Membentuk context metrik

Method evaluator mengumpulkan nilai setiap kelompok:

```php
$groupScores = [];
$weightedTotal = 0.0;
$weightTotal = 0.0;

foreach ($evaluated as $item) {
    if (! is_numeric($item['score'] ?? null)) {
        continue;
    }

    $itemScore = (float) $item['score'];
    $weight = is_numeric($item['weight'] ?? null)
        ? (float) $item['weight']
        : 0.0;

    if ($weight > 0) {
        $weightedTotal += $itemScore * $weight;
        $weightTotal += $weight;
    }

    $group = $item['aggregation_key'] ?? null;
    if ($group !== null) {
        $groupScores[(string) $group][] = $itemScore;
    }
}
```

Untuk skala LAM INFOKOM 1–4, total berbobot dinormalisasi dengan `score_scale_max` atau default 4:

```php
$scoreScaleMax = 4.0;

foreach ($qualificationRules as $rule) {
    $parameters = is_array($rule->parameters)
        ? $rule->parameters
        : [];

    if (
        is_numeric($parameters['score_scale_max'] ?? null)
        && (float) $parameters['score_scale_max'] > 0
    ) {
        $scoreScaleMax = (float) $parameters['score_scale_max'];
        break;
    }
}

$context = [
    'final_score' => $weightTotal > 0
        ? round($weightedTotal / $scoreScaleMax, 4)
        : $score,
    'average_score' => $score,
    'score' => $score,
];
```

Jika bobot total adalah 400 dan semua skor bernilai 4, maka `weightedTotal` bernilai 1600 dan `final_score` menjadi 400 setelah dibagi skala maksimum 4.

Metrik kelompok kemudian dibentuk:

```php
foreach ($groupScores as $group => $groupValues) {
    $context[$group . '.item_min'] = min($groupValues);
    $context[$group . '.item_average'] = round(
        (float) (array_sum($groupValues) / count($groupValues)),
        4,
    );
    $context[$group . '.item_count'] = count($groupValues);
}
```

## 5. Mengevaluasi beberapa rule dan memilih status terbaik

Rule diurutkan berdasarkan `validity_years` menurun. Dengan demikian, rule lima tahun dicoba sebelum rule tiga tahun:

```php
usort($rules, function (
    InstrumentScoringRule $a,
    InstrumentScoringRule $b,
): int {
    $aParameters = is_array($a->parameters)
        ? $a->parameters
        : [];
    $bParameters = is_array($b->parameters)
        ? $b->parameters
        : [];

    return (int) ($bParameters['validity_years'] ?? 0)
        <=> (int) ($aParameters['validity_years'] ?? 0);
});
```

Setiap rule kemudian diuji. Jika rule lima tahun gagal, evaluator melanjutkan ke rule tiga tahun. Jika semua gagal, status menjadi `not_qualified`.

## 6. Expression `all`, `any`, dan operator perbandingan

Contoh expression:

```json
{
  "all": [
    {"metric": "final_score", "gte": 321},
    {"metric": "Budaya Mutu.item_min", "gte": 3},
    {"metric": "Relevansi Pendidikan.item_min", "gte": 3}
  ]
}
```

Evaluator mendukung:

```php
$passed = match (true) {
    array_key_exists('between', $expression)
        && is_array($expression['between'])
        => $actual >= (float) ($expression['between'][0] ?? INF)
        && $actual <= (float) ($expression['between'][1] ?? -INF),

    array_key_exists('gte', $expression)
        => $actual >= (float) $expression['gte'],

    array_key_exists('lte', $expression)
        => $actual <= (float) $expression['lte'],

    array_key_exists('gt', $expression)
        => $actual > (float) $expression['gt'],

    array_key_exists('lt', $expression)
        => $actual < (float) $expression['lt'],

    array_key_exists('eq', $expression)
        => abs($actual - (float) $expression['eq']) < 0.000001,

    default => false,
};
```

Expression `all` mengharuskan semua kondisi terpenuhi. Expression `any` mengharuskan minimal satu kondisi terpenuhi. Setiap kegagalan dicatat agar pengguna dapat mengetahui gate yang belum tercapai.

## 7. Hard gate rerata kriteria utama

Untuk rule yang mempunyai parameter `min_average`, evaluator memeriksa tiga kelompok utama:

```php
if ($passed && is_numeric($minimumAverage)) {
    foreach ([
        'Budaya Mutu',
        'Relevansi Pendidikan',
        'Relevansi Penelitian',
    ] as $group) {
        $average = $context[$group . '.item_average'] ?? null;

        if (
            $average === null
            || (float) $average < (float) $minimumAverage
        ) {
            $passed = false;
            $failures[] = $group
                . '.item_average harus minimal '
                . $minimumAverage;
        }
    }
}
```

Dengan konfigurasi `min_average: 3.2`, nilai akhir yang tinggi tetap tidak cukup apabila rata-rata salah satu kriteria utama masih di bawah 3,20.

## 8. Menyimpan status ke immutable snapshot

`scoreAndPersist()` menggunakan status hasil qualification:

```php
return AccreditationScoreSnapshot::create([
    'accreditation_id' => $accreditation->getKey(),
    'instrument_version_id' => $result['instrument_version_id'],
    'calculated_by' => $userId,
    'score' => $result['score'],
    'status' => $result['status'],
    'snapshot_hash' => hash('sha256', $canonical),
    'rule_results' => $result['rules'],
    'input_snapshot' => $inputSnapshot,
    'calculated_at' => now(),
]);
```

Snapshot tetap immutable. Status, rule yang lulus, context, masa berlaku, dan alasan kegagalan ikut masuk ke data JSON `rule_results` dan tercakup dalam `snapshot_hash`.

## 9. Contoh konfigurasi LAM INFOKOM

```json
{
  "code": "LAM21-UNGGUL-5",
  "rule_type": "status_qualification",
  "expression": {
    "all": [
      {"metric": "final_score", "gte": 361},
      {"metric": "Budaya Mutu.item_min", "gte": 3},
      {"metric": "Relevansi Pendidikan.item_min", "gte": 3},
      {"metric": "Relevansi Penelitian.item_min", "gte": 3}
    ]
  },
  "parameters": {
    "status": "unggul_5_tahun",
    "validity_years": 5,
    "min_average": 3.2,
    "score_scale_max": 4
  }
}
```

Rule tiga tahun menggunakan expression yang sama dengan `final_score >= 321` dan `validity_years: 3`.

## 10. Pengujian yang tersedia

Regression test tersedia pada `tests/Unit/StatusQualificationEvaluatorTest.php`. Test tersebut memverifikasi bahwa rule lima tahun dipilih ketika semua gate terpenuhi, serta status berubah menjadi `not_qualified` ketika nilai minimum item atau rata-rata kelompok gagal.

Validasi terakhir menghasilkan:

```text
25 test passed
63 assertions
Tidak ada syntax error
```

## Catatan implementasi

Runtime saat ini menggunakan nama metrik kriteria yang harus sama persis dengan nilai `aggregation_key`, misalnya `Budaya Mutu`. Karena itu, manifest import harus menjaga konsistensi kode atau nama agregasi. Untuk implementasi production yang lebih kuat, disarankan menggunakan `aggregation_key` teknis yang stabil, misalnya `CULTURE_QUALITY`, lalu menyimpan nama Indonesia hanya sebagai label.

Selain itu, `status_qualification` baru aktif jika rule tersebut sudah tersedia pada instrument version yang sedang digunakan. Rule yang hanya tersimpan di file manifest tetapi belum di-commit dan belum terikat ke instrument version tidak akan dieksekusi.
