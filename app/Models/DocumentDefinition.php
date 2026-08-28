<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentDefinition extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'domain', 'scope_type', 'supported_formats', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['supported_formats' => 'array', 'is_active' => 'boolean'];
    }

    public function templateVersions(): HasMany
    {
        return $this->hasMany(DocumentTemplateVersion::class);
    }

    public function generationRequests(): HasMany
    {
        return $this->hasMany(DocumentGenerationRequest::class);
    }
}
