<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentScale extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_version_id', 'code', 'name', 'scale_type', 'min_value', 'max_value', 'precision'];

    protected function casts(): array
    {
        return ['min_value' => 'decimal:4', 'max_value' => 'decimal:4', 'precision' => 'integer'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(AssessmentScaleOption::class);
    }
}
