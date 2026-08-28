<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentEvidenceReference extends Model
{
    use HasFactory;
    protected $fillable = ['document_artifact_id', 'evidence_id', 'evidence_version_id', 'label', 'external_url', 'citation_page', 'citation_note'];
    protected function casts(): array { return ['citation_page' => 'integer']; }
    public function artifact(): BelongsTo { return $this->belongsTo(DocumentArtifact::class, 'document_artifact_id'); }
    public function evidence(): BelongsTo { return $this->belongsTo(Evidence::class); }
    public function evidenceVersion(): BelongsTo { return $this->belongsTo(EvidenceVersion::class, 'evidence_version_id'); }
}
