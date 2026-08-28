<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpmiEvaluation extends Model
{
    use HasFactory;

    protected $table = 'spmi_evaluations';

    protected $fillable = ['spmi_realization_id', 'perguruan_tinggi_id', 'program_studi_id', 'result', 'achievement_percentage', 'analysis', 'root_cause', 'recommendation', 'status', 'evaluated_by', 'evaluated_at'];

    protected function casts(): array
    {
        return ['achievement_percentage' => 'decimal:4', 'evaluated_at' => 'datetime'];
    }

    public function realization(): BelongsTo
    {
        return $this->belongsTo(SpmiRealization::class, 'spmi_realization_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function improvementPrograms(): HasMany
    {
        return $this->hasMany(SpmiImprovementProgram::class);
    }
}
