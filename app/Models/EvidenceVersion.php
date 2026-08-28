<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'evidence_id', 'document_id', 'created_by', 'version_no', 'change_reason',
        'manifest_hash', 'locked_at',
    ];

    protected function casts(): array
    {
        return ['locked_at' => 'datetime'];
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
