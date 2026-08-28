<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput;

use App\Models\Accreditation;
use App\Models\AmiCycle;
use App\Models\DocumentArtifact;
use App\Models\DocumentGenerationRequest;
use App\Models\DocumentSnapshot;
use App\Models\EvidenceCollection;
use App\Models\ReadinessRun;
use App\Models\RtlAction;
use App\Models\SpmiEvaluation;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class GenericReportService
{
    public function __construct(private readonly DocumentOutputService $output) {}

    public function generate(DocumentGenerationRequest $request): DocumentArtifact
    {
        $request->loadMissing('definition', 'requester', 'perguruanTinggi', 'programStudi');
        $definition = $request->definition;
        if (! $definition) throw new RuntimeException('Definisi dokumen tidak ditemukan.');

        $request->update(['status' => 'processing', 'started_at' => now(), 'error_message' => null]);
        try {
            $payload = $this->payload($request);
            $snapshot = $this->output->snapshot($request, $payload, 'Document Output Engine / Laporan Generik');
            $html = $this->render($request, $payload);
            $path = 'document-output/' . $request->getKey() . '/laporan.html';
            Storage::disk('local')->put($path, $html);
            $artifact = $request->artifacts()->create([
                'document_snapshot_id' => $snapshot->getKey(), 'format' => 'html',
                'file_name' => 'laporan-' . $request->getKey() . '.html', 'storage_provider' => 'local',
                'storage_path' => $path, 'mime_type' => 'text/html', 'size_bytes' => strlen($html),
                'sha256' => hash('sha256', $html), 'status' => 'draft',
            ]);
            $request->update(['status' => 'completed', 'completed_at' => now()]);
            return $artifact;
        } catch (\Throwable $exception) {
            $request->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            throw $exception;
        }
    }

    public function payload(DocumentGenerationRequest $request): array
    {
        $ptId = $request->perguruan_tinggi_id;
        $prodiId = $request->program_studi_id;
        $definitionCode = strtolower((string) ($request->definition?->code ?? ''));
        $summary = ['spmi' => [], 'ami' => [], 'rtl' => [], 'evidence' => [], 'readiness' => [], 'akreditasi' => []];
        if (str_contains($definitionCode, 'spmi') || $definitionCode === '') $summary['spmi'] = ['evaluasi' => SpmiEvaluation::query()->count()];
        if (str_contains($definitionCode, 'ami') || $definitionCode === '') $summary['ami'] = ['siklus' => AmiCycle::query()->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))->count()];
        if (str_contains($definitionCode, 'rtl') || $definitionCode === '') $summary['rtl'] = ['tindakan' => RtlAction::query()->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))->when($prodiId, fn ($q) => $q->where('program_studi_id', $prodiId))->count()];
        if (str_contains($definitionCode, 'evidence') || $definitionCode === '') $summary['evidence'] = ['koleksi' => EvidenceCollection::query()->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))->count()];
        if (str_contains($definitionCode, 'readiness') || str_contains($definitionCode, 'mutu') || $definitionCode === '') $summary['readiness'] = ['run' => ReadinessRun::query()->when($ptId, fn ($q) => $q->whereHas('accreditation', fn ($a) => $a->where('perguruan_tinggi_id', $ptId)))->count(), 'terakhir' => ReadinessRun::query()->latest('id')->value('completion_percent')];
        if (str_contains($definitionCode, 'akreditasi') || str_contains($definitionCode, 'accreditation') || $definitionCode === '') $summary['akreditasi'] = ['kegiatan' => Accreditation::query()->when($ptId, fn ($q) => $q->where('perguruan_tinggi_id', $ptId))->count()];
        return ['judul' => $request->definition?->name, 'kode' => $request->definition?->code, 'periode' => $request->period_label, 'scope' => ['perguruan_tinggi' => $request->perguruanTinggi?->nama_pt, 'program_studi' => $request->programStudi?->nama_prodi], 'dibuat_pada' => now()->toIso8601String(), 'ringkasan' => $summary];
    }

    private function render(DocumentGenerationRequest $request, array $payload): string
    {
        $title = e((string) ($payload['judul'] ?? 'Laporan SQM'));
        $scope = e(trim(($payload['scope']['perguruan_tinggi'] ?? '') . ' / ' . ($payload['scope']['program_studi'] ?? ''), ' /'));
        $rows = '';
        foreach ($payload['ringkasan'] as $module => $data) foreach ($data as $key => $value) $rows .= '<tr><td>' . e(strtoupper($module)) . '</td><td>' . e(str_replace('_', ' ', $key)) . '</td><td>' . e((string) ($value ?? '-')) . '</td></tr>';
        return '<!doctype html><html lang="id"><head><meta charset="utf-8"><title>' . $title . '</title><style>body{font-family:Arial,sans-serif;color:#172033;margin:40px}h1{color:#b45309}table{border-collapse:collapse;width:100%;margin-top:24px}th,td{border:1px solid #dbe1ea;padding:10px;text-align:left}th{background:#f3f4f6}.meta{color:#5b6472}</style></head><body><h1>' . $title . '</h1><p class="meta">Scope: ' . $scope . '<br>Periode: ' . e((string) ($payload['periode'] ?? '-')) . '<br>Dibuat: ' . e((string) ($payload['dibuat_pada'] ?? '-')) . '</p><h2>Ringkasan Sistem Mutu</h2><table><thead><tr><th>Modul</th><th>Indikator</th><th>Nilai</th></tr></thead><tbody>' . $rows . '</tbody></table><p>Dokumen ini merupakan laporan generik SQM. Struktur resmi LED/LKPS/LKPT belum diterapkan dan akan menggunakan versi template khusus setelah tersedia.</p></body></html>';
    }
}
