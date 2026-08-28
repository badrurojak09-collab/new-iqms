<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiImprovementProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'spmi_improvement_programs';

    protected $fillable = ['spmi_evaluation_id', 'effectiveness_review_id', 'spmi_indicator_id', 'spmi_target_id', 'perguruan_tinggi_id', 'program_studi_id', 'accreditation_id', 're_evaluation_status', 're_evaluation_error', 're_evaluation_requested_at', 'code', 'title', 'action_plan', 'owner_id', 'due_date', 'progress_percent', 'status', 'completion_notes', 'verified_by', 'verified_at'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'progress_percent' => 'integer', 'verified_at' => 'datetime', 're_evaluated_at' => 'datetime', 're_evaluation_requested_at' => 'datetime'];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(SpmiIndicator::class, 'spmi_indicator_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(SpmiTarget::class, 'spmi_target_id');
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class, 'accreditation_id');
    }

    public function reEvaluatedReadinessRun(): BelongsTo
    {
        return $this->belongsTo(ReadinessRun::class, 're_evaluated_readiness_run_id');
    }

    public function effectivenessReview(): BelongsTo
    {
        return $this->belongsTo(RtlEffectivenessReview::class, 'effectiveness_review_id');
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(SpmiEvaluation::class, 'spmi_evaluation_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
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
