<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccreditationResponse extends Model
{
    use HasFactory;

    protected $table = 'accreditation_responses';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_REVISION_REQUIRED = 'revision_required';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_LOCKED = 'locked';

    protected $fillable = ['accreditation_id', 'accreditation_section_id', 'instrument_node_id', 'response_key', 'response_type', 'response_text', 'response_numeric', 'response_json', 'status', 'last_edited_by', 'submitted_at', 'reviewed_by', 'reviewed_at', 'review_notes', 'approved_by', 'approved_at', 'locked_by', 'locked_at', 'revision_no'];

    protected function casts(): array
    {
        return [
            'response_numeric' => 'decimal:6',
            'response_json' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'revision_no' => 'integer',
        ];
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(AccreditationResponseRevision::class, 'accreditation_response_id')->latest('revision_no');
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED || $this->locked_at !== null;
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
