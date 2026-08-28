<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentRubric extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_version_id', 'instrument_node_id', 'assessment_scale_option_id', 'min_score', 'max_score', 'label', 'description', 'evidence_expectation', 'status', 'approved_by', 'approved_at', 'approval_notes'];

    protected function casts(): array
    {
        return ['min_score' => 'decimal:4', 'max_score' => 'decimal:4', 'approved_at' => 'datetime'];
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function scaleOption(): BelongsTo
    {
        return $this->belongsTo(AssessmentScaleOption::class, 'assessment_scale_option_id');
    }
}
