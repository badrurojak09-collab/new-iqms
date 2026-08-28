<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentGenerationRequest extends Model
{
    use HasFactory;

    protected $fillable = ['document_definition_id', 'document_template_version_id', 'perguruan_tinggi_id', 'program_studi_id', 'requested_by', 'period_label', 'parameters', 'status', 'error_message', 'started_at', 'completed_at'];
    protected function casts(): array { return ['parameters' => 'array', 'started_at' => 'datetime', 'completed_at' => 'datetime']; }
    public function definition(): BelongsTo { return $this->belongsTo(DocumentDefinition::class, 'document_definition_id'); }
    public function templateVersion(): BelongsTo { return $this->belongsTo(DocumentTemplateVersion::class, 'document_template_version_id'); }
    public function perguruanTinggi(): BelongsTo { return $this->belongsTo(PerguruanTinggi::class); }
    public function programStudi(): BelongsTo { return $this->belongsTo(ProgramStudi::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function snapshots(): HasMany { return $this->hasMany(DocumentSnapshot::class); }
    public function artifacts(): HasMany { return $this->hasMany(DocumentArtifact::class); }
}
