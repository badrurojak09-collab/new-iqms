<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadinessRun extends Model
{
    use HasFactory;

    protected $fillable = ['accreditation_id', 'instrument_version_id', 'created_by', 'run_type', 'status', 'engine_version', 'total_items', 'ready_items', 'completion_percent', 'weighted_score', 'input_hash', 'summary', 'started_at', 'completed_at', 'error_message'];

    protected function casts(): array
    {
        return ['completion_percent' => 'decimal:4', 'weighted_score' => 'decimal:6', 'summary' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ReadinessResult::class);
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(ReadinessGap::class);
    }
}
