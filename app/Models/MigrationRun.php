<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MigrationRun extends Model
{
    use HasFactory;

    protected $table = 'migration_runs';

    protected $fillable = ['run_uuid', 'source_name', 'source_checksum', 'mode', 'status', 'total_rows', 'migrated_rows', 'skipped_rows', 'exception_rows', 'started_at', 'finished_at', 'notes'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(MigrationLedger::class);
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(MigrationException::class);
    }
}
