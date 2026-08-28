<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCriterion extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_version_id', 'instrument_node_id', 'code', 'name', 'weight', 'minimum_score', 'sort_order', 'is_required'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'minimum_score' => 'decimal:4', 'is_required' => 'boolean'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(AssessmentElement::class);
    }
}
