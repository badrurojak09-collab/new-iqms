<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MigrationException extends Model
{
    use HasFactory;

    protected $table = 'migration_exceptions';

    protected $fillable = ['migration_run_id', 'source_table', 'source_pk', 'reason_code', 'reason', 'payload_fingerprint', 'payload', 'status', 'resolved_by', 'resolved_at'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'resolved_at' => 'datetime'];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(MigrationRun::class, 'migration_run_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
