<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationSection extends Model
{
    use HasFactory;

    protected $table = 'accreditation_sections';

    protected $fillable = ['accreditation_id', 'instrument_node_id', 'code', 'title', 'section_type', 'sort_order', 'status', 'readiness_percent'];

    protected function casts(): array
    {
        return ['readiness_percent' => 'decimal:4'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AccreditationResponse::class);
    }
}
