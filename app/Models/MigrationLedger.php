<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationLedger extends Model
{
    use HasFactory;

    protected $table = 'migration_ledgers';

    protected $fillable = ['migration_run_id', 'source_table', 'source_pk', 'target_table', 'target_id', 'source_fingerprint', 'status', 'message'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(MigrationRun::class, 'migration_run_id');
    }
}
