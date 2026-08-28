<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceCollectionItem extends Model
{
    use HasFactory;

    protected $fillable = ['evidence_collection_id', 'evidence_id', 'requirement_code', 'requirement_title', 'target_type', 'target_id', 'is_required', 'status', 'notes'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean'];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(EvidenceCollection::class, 'evidence_collection_id');
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }
}
