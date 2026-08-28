<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MigrationException;
use App\Models\MigrationLedger;
use App\Models\MigrationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class LegacyMigrationImport extends Command
{
    protected $signature = 'legacy:migrate-import {source : File manifest CSV atau JSON} {--notes= : Catatan import}';

    protected $description = 'Import manifest legacy secara idempotent ke migration ledger tanpa menulis data target.';

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        if (! is_file($source) || ! is_readable($source)) {
            $this->error("Sumber legacy tidak dapat dibaca: {$source}");
            return self::FAILURE;
        }
        $rows = $this->readRows($source);
        $run = MigrationRun::query()->create([
            'run_uuid' => (string) Str::uuid(), 'source_name' => basename($source),
            'source_checksum' => hash_file('sha256', $source), 'mode' => 'dual_run', 'status' => 'running',
            'started_at' => now(), 'notes' => $this->option('notes') ?: 'Manifest dicatat; tidak ada penulisan target.',
        ]);
        $migrated = $skipped = $exceptions = 0;
        DB::transaction(function () use ($rows, $run, &$migrated, &$skipped, &$exceptions): void {
            foreach ($rows as $index => $row) {
                $sourceTable = trim((string) ($row['source_table'] ?? ''));
                $sourcePk = trim((string) ($row['source_pk'] ?? ($index + 1)));
                $targetTable = trim((string) ($row['target_table'] ?? ''));
                $fingerprint = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
                if ($sourceTable === '' || $targetTable === '') {
                    MigrationException::query()->create(['migration_run_id' => $run->id, 'source_table' => $sourceTable ?: 'unknown', 'source_pk' => $sourcePk, 'reason_code' => 'INVALID_MANIFEST', 'reason' => 'source_table dan target_table wajib diisi.', 'payload_fingerprint' => $fingerprint, 'payload' => $row]);
                    $exceptions++;
                    continue;
                }
                $existing = MigrationLedger::query()->where(['source_table' => $sourceTable, 'source_pk' => $sourcePk, 'target_table' => $targetTable])->first();
                if ($existing) {
                    $skipped++;
                    continue;
                }
                MigrationLedger::query()->create(['migration_run_id' => $run->id, 'source_table' => $sourceTable, 'source_pk' => $sourcePk, 'target_table' => $targetTable, 'target_id' => isset($row['target_id']) && $row['target_id'] !== '' ? (int) $row['target_id'] : null, 'source_fingerprint' => $fingerprint, 'status' => 'review_required', 'message' => $row['message'] ?? 'Menunggu review dan mapping target.']);
                $migrated++;
            }
        });
        $run->update(['status' => 'completed', 'total_rows' => count($rows), 'migrated_rows' => $migrated, 'skipped_rows' => $skipped, 'exception_rows' => $exceptions, 'finished_at' => now(), 'notes' => ($run->notes ? $run->notes . ' ' : '') . ($exceptions > 0 ? "{$exceptions} exception perlu direview." : 'Tidak ada exception.')]);
        $this->info("Import manifest selesai: {$run->run_uuid}");
        $this->line("Total: " . count($rows) . "; baru: {$migrated}; dilewati: {$skipped}; exception: {$exceptions}.");
        return self::SUCCESS;
    }

    /** @return list<array<string, mixed>> */
    private function readRows(string $source): array
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        if ($extension === 'json') {
            $data = json_decode((string) file_get_contents($source), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) throw new RuntimeException('JSON manifest harus berupa array baris.');
            return array_values(array_filter($data, 'is_array'));
        }
        $file = new \SplFileObject($source);
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $headers = null; $rows = [];
        foreach ($file as $row) {
            if (! is_array($row) || $row === [null]) continue;
            if ($headers === null) { $headers = array_map(fn ($value): string => trim((string) $value), $row); continue; }
            $rows[] = array_combine($headers, array_pad($row, count($headers), null)) ?: [];
        }
        return $rows;
    }
}
