<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = ['document_definition_id', 'version_label', 'format', 'accreditation_body', 'instrument_version', 'schema', 'template_hash', 'status', 'created_by', 'published_by', 'published_at'];
    protected function casts(): array { return ['schema' => 'array', 'published_at' => 'datetime']; }
    public function definition(): BelongsTo { return $this->belongsTo(DocumentDefinition::class, 'document_definition_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function publisher(): BelongsTo { return $this->belongsTo(User::class, 'published_by'); }
    public function generationRequests(): HasMany { return $this->hasMany(DocumentGenerationRequest::class); }
}
