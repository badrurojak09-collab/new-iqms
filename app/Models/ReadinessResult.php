<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadinessResult extends Model
{
    use HasFactory;

    protected $fillable = ['readiness_run_id', 'instrument_node_id', 'assessment_element_id', 'item_key', 'status', 'weight', 'completion_percent', 'evidence_percent', 'score', 'gap_count', 'details'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'completion_percent' => 'decimal:4', 'evidence_percent' => 'decimal:4', 'score' => 'decimal:6', 'details' => 'array'];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReadinessRun::class, 'readiness_run_id');
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function assessmentElement(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class);
    }

    public function mappingResults(): HasMany
    {
        return $this->hasMany(ReadinessMappingResult::class);
    }

    public function gaps(): HasMany
    {
        return $this->hasMany(ReadinessGap::class);
    }
}
