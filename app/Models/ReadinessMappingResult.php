<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadinessMappingResult extends Model
{
    use HasFactory;

    protected $fillable = ['readiness_result_id', 'instrument_mapping_id', 'source_indicator_id', 'coverage_weight', 'source_completion_percent', 'source_evidence_percent', 'status', 'gap_reason', 'details'];

    protected function casts(): array
    {
        return ['coverage_weight' => 'decimal:4', 'source_completion_percent' => 'decimal:4', 'source_evidence_percent' => 'decimal:4', 'details' => 'array'];
    }

    public function readinessResult(): BelongsTo
    {
        return $this->belongsTo(ReadinessResult::class, 'readiness_result_id');
    }

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(InstrumentMapping::class, 'instrument_mapping_id');
    }

    public function sourceIndicator(): BelongsTo
    {
        return $this->belongsTo(AssessmentIndicator::class, 'source_indicator_id');
    }
}
