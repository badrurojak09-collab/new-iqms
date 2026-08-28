<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentScaleOption extends Model
{
    use HasFactory;

    protected $fillable = ['assessment_scale_id', 'code', 'label', 'numeric_value', 'sort_order', 'metadata'];

    protected function casts(): array
    {
        return ['numeric_value' => 'decimal:4', 'metadata' => 'array'];
    }

    public function scale(): BelongsTo
    {
        return $this->belongsTo(AssessmentScale::class, 'assessment_scale_id');
    }
}
