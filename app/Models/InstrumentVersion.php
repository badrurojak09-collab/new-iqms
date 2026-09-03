<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ImmutableInstrumentVersion;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrumentVersion extends Model
{
    use HasFactory, ImmutableInstrumentVersion, SoftDeletes;

    protected $fillable = [
        'instrument_family_id',
        'parent_version_id',
        'version_label',
        'status',
        'source_reference',
        'effective_from',
        'effective_until',
        'content_hash',
        'changelog',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'published_at' => 'datetime',
            'changelog' => 'array',
        ];
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(InstrumentFamily::class, 'instrument_family_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_version_id');
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(InstrumentNode::class);
    }

    public function assessmentCriteria(): HasMany
    {
        return $this->hasMany(AssessmentCriterion::class);
    }

    public function assessmentScales(): HasMany
    {
        return $this->hasMany(AssessmentScale::class);
    }

    public function thresholds(): HasMany
    {
        return $this->hasMany(AssessmentThreshold::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(InstrumentMapping::class);
    }

    public function assessmentRubrics(): HasMany
    {
        return $this->hasMany(AssessmentRubric::class);
    }

    public function lkpsTemplates(): HasMany
    {
        return $this->hasMany(LkpsTemplate::class);
    }

    public function ledTemplates(): HasMany
    {
        return $this->hasMany(LedTemplate::class);
    }
}
