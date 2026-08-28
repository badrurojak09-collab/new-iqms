<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentIndicator extends Model
{
    use HasFactory;

    protected $fillable = ['assessment_element_id', 'code', 'name', 'unit', 'direction', 'data_type', 'target_definition', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return ['target_definition' => 'array', 'is_required' => 'boolean'];
    }

    public function thresholds(): HasMany
    {
        return $this->hasMany(AssessmentThreshold::class, 'assessment_indicator_id');
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(AssessmentElement::class, 'assessment_element_id');
    }
}
