<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentThreshold extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_version_id', 'assessment_element_id', 'assessment_indicator_id', 'assessment_scale_id', 'assessment_rubric_id', 'code', 'name', 'comparison', 'target_value', 'min_value', 'max_value', 'pass_score', 'fail_score', 'minimum_score', 'weight', 'status', 'config', 'source_reference', 'direction', 'aggregation_key', 'aggregation_operator', 'aggregation_min_passed', 'sequence', 'approved_by', 'approved_at', 'approval_notes'];

    protected function casts(): array
    {
        return ['target_value' => 'decimal:6', 'min_value' => 'decimal:6', 'max_value' => 'decimal:6', 'pass_score' => 'decimal:6', 'fail_score' => 'decimal:6', 'minimum_score' => 'decimal:6', 'weight' => 'decimal:4', 'aggregation_min_passed' => 'integer', 'sequence' => 'integer', 'config' => 'array', 'approved_at' => 'datetime'];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'assessment_element_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(AssessmentIndicator::class, 'assessment_indicator_id');
    }

    public function scale(): BelongsTo
    {
        return $this->belongsTo(AssessmentScale::class, 'assessment_scale_id');
    }

    public function rubric(): BelongsTo
    {
        return $this->belongsTo(AssessmentRubric::class, 'assessment_rubric_id');
    }
}
