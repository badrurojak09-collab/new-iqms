<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceLinkCheck extends Model
{
    use HasFactory;

    protected $fillable = ['evidence_id', 'evidence_version_id', 'checked_by', 'status', 'http_status', 'url_hash', 'notes', 'checked_at'];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function evidence(): BelongsTo
    {
        return $this->belongsTo(Evidence::class);
    }

    public function evidenceVersion(): BelongsTo
    {
        return $this->belongsTo(EvidenceVersion::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
