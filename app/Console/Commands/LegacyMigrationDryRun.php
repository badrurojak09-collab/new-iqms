<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MigrationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;

final class LegacyMigrationDryRun extends Command
{
    protected $signature = 'legacy:migrate-dry-run {source : Path to legacy SQL/JSON/CSV source} {--notes= : Optional run notes}';

    protected $description = 'Register a reproducible, non-destructive legacy migration dry run.';

    public function handle(): int
    {
        $source = (string) $this->argument('source');
        if (! is_file($source) || ! is_readable($source)) {
            throw new RuntimeException("Legacy source tidak dapat dibaca: {$source}");
        }

        $checksum = hash_file('sha256', $source);
        $run = MigrationRun::query()->create([
            'run_uuid' => (string) Str::uuid(),
            'source_name' => basename($source),
            'source_checksum' => $checksum,
            'mode' => 'dry_run',
            'status' => 'completed',
            'started_at' => now(),
            'finished_at' => now(),
            'notes' => $this->option('notes') ?: 'No target writes performed. Use mapping workers after review.',
        ]);

        $this->info("Dry run {$run->run_uuid} registered.");
        $this->line("Source SHA-256: {$checksum}");
        $this->warn('Tidak ada data target yang ditulis. Mapping/exceptions harus direview sebelum import.');

        return self::SUCCESS;
    }
}
