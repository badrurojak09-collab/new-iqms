<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RtlEffectivenessReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['rtl_action_id', 'spmi_evaluation_id', 'reviewed_by', 'outcome', 'effectiveness_score', 'observed_result', 'evidence_summary', 'recommendation', 'ppepp_stage', 'follow_up_required', 'status', 'reviewed_at'];

    protected function casts(): array
    {
        return ['effectiveness_score' => 'integer', 'follow_up_required' => 'boolean', 'reviewed_at' => 'datetime'];
    }

    public function rtlAction(): BelongsTo
    {
        return $this->belongsTo(RtlAction::class);
    }

    public function spmiEvaluation(): BelongsTo
    {
        return $this->belongsTo(SpmiEvaluation::class, 'spmi_evaluation_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evidenceLinks(): MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }

    public function improvementPrograms(): HasMany
    {
        return $this->hasMany(SpmiImprovementProgram::class, 'effectiveness_review_id');
    }
}
