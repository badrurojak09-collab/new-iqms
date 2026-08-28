<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationCriterion extends Model
{
    use HasFactory;

    protected $table = 'accreditation_criteria';

    protected $fillable = ['instrument_version_id', 'code', 'name', 'description', 'sort_order', 'is_required', 'metadata'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'metadata' => 'array'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(InstrumentMapping::class, 'accreditation_criterion_id');
    }
}
