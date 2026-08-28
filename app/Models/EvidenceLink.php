<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EvidenceLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_id', 'linkable_type', 'linkable_id', 'relation_type',
        'citation_page', 'citation_note', 'is_required',
    ];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
