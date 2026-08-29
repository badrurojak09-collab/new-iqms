<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class DocumentArtifact extends Model
{
    use HasFactory;

    protected $fillable = ['document_generation_request_id', 'document_snapshot_id', 'format', 'file_name', 'storage_provider', 'storage_path', 'external_url', 'mime_type', 'size_bytes', 'sha256', 'status'];

    protected function casts(): array
    {
        return ['size_bytes' => 'integer'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentGenerationRequest::class, 'document_generation_request_id');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(DocumentSnapshot::class, 'document_snapshot_id');
    }

    public function evidenceReferences(): HasMany
    {
        return $this->hasMany(DocumentEvidenceReference::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }
}
