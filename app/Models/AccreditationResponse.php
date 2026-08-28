<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AccreditationResponse extends Model
{
    use HasFactory;

    protected $table = 'accreditation_responses';

    protected $fillable = ['accreditation_id', 'accreditation_section_id', 'instrument_node_id', 'response_key', 'response_type', 'response_text', 'response_numeric', 'response_json', 'status', 'last_edited_by', 'submitted_at'];

    protected function casts(): array
    {
        return ['response_numeric' => 'decimal:6', 'response_json' => 'array', 'submitted_at' => 'datetime'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AccreditationSection::class, 'accreditation_section_id');
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function evidenceLinks(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
