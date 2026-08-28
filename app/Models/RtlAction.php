<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RtlAction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rtl_actions';

    protected $fillable = ['perguruan_tinggi_id', 'program_studi_id', 'ami_finding_id', 'rtm_decision_id', 'readiness_gap_id', 'owner_id', 'code', 'title', 'action_plan', 'due_date', 'progress_percent', 'status', 'evidence_of_completion', 'verified_by', 'verified_at'];

    protected $attributes = ['status' => 'open', 'progress_percent' => 0];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'progress_percent' => 'integer', 'verified_at' => 'datetime'];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function effectivenessReviews(): HasMany
    {
        return $this->hasMany(RtlEffectivenessReview::class);
    }

    public function evidenceLinks(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }

    public function readinessGap(): BelongsTo
    {
        return $this->belongsTo(ReadinessGap::class, 'readiness_gap_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(AmiFinding::class, 'ami_finding_id');
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(RtmDecision::class, 'rtm_decision_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
