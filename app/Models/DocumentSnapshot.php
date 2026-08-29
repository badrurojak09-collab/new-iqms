<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class DocumentSnapshot extends Model
{
    use HasFactory;

    protected $fillable = ['document_generation_request_id', 'payload', 'payload_hash', 'source_context'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(DocumentGenerationRequest::class, 'document_generation_request_id');
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(DocumentArtifact::class);
    }
}
