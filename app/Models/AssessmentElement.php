<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentElement extends Model
{
    use HasFactory;

    protected $fillable = ['assessment_criterion_id', 'instrument_node_id', 'code', 'title', 'element_type', 'weight', 'is_required', 'sort_order', 'metadata'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'is_required' => 'boolean', 'metadata' => 'array'];
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(AssessmentCriterion::class, 'assessment_criterion_id');
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function thresholds(): HasMany
    {
        return $this->hasMany(AssessmentThreshold::class, 'assessment_element_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(AssessmentIndicator::class);
    }
}
