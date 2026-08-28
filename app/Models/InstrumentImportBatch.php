<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentImportBatch extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_family_id', 'created_by', 'original_name', 'format', 'source_hash', 'status', 'total_rows', 'valid_rows', 'error_rows', 'summary', 'committed_at'];

    protected function casts(): array
    {
        return ['summary' => 'array', 'committed_at' => 'datetime'];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(InstrumentFamily::class, 'instrument_family_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(InstrumentImportRow::class);
    }
}
