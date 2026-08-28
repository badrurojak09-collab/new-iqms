<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentMapping extends Model
{
    use HasFactory;

    protected $table = 'instrument_mappings';

    protected $fillable = [
        'instrument_version_id', 'instrument_node_id', 'source_indicator_id', 'accreditation_criterion_id',
        'target_element_id', 'mapping_type', 'source_type', 'target_type', 'target_key', 'coverage_weight',
        'is_required', 'approval_status', 'approved_by', 'approved_at', 'source_reference', 'notes',
    ];

    protected function casts(): array
    {
        return ['coverage_weight' => 'decimal:4', 'is_required' => 'boolean', 'approved_at' => 'datetime'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function sourceIndicator(): BelongsTo
    {
        return $this->belongsTo(AssessmentIndicator::class, 'source_indicator_id');
    }

    public function targetElement(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'target_element_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function readinessMappingResults(): HasMany
    {
        return $this->hasMany(ReadinessMappingResult::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(AccreditationCriterion::class, 'accreditation_criterion_id');
    }
}
