<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Akreditasi</title>
    <style>
        @page { margin: 28px 30px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2937; }
        h1 { font-size: 17px; margin: 0 0 5px; color: #92400e; }
        .meta { color: #6b7280; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #fef3c7; color: #78350f; text-align: left; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Progress Akreditasi</h1>
    <div class="meta">Dibuat pada {{ now()->format('Y-m-d H:i') }} | Perguruan Tinggi ID: {{ $perguruanTinggiId }}</div>
    <table>
        <thead><tr><th>Kode</th><th>Judul</th><th>Scope/Prodi</th><th>Instrumen</th><th>Status</th><th>LED %</th><th>LKPS %</th><th>Response</th><th>Readiness</th></tr></thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $row['code'] }}</td><td>{{ $row['title'] }}</td><td>{{ $row['scope'] }} / {{ $row['program_studi'] }}</td><td>{{ $row['instrument_version'] }}</td><td>{{ $row['status'] }}</td>
                <td class="number">{{ $row['led_progress'] }}</td><td class="number">{{ $row['lkps_progress'] }}</td><td class="number">{{ $row['responses_completed'] }}/{{ $row['responses'] }}</td><td class="number">{{ $row['readiness_completed'] }}/{{ $row['readiness_items'] }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Tidak ada data akreditasi pada filter yang dipilih.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
