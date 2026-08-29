<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SpmiEvaluation extends Model
{
    use HasFactory, ScopedByTenant;

    protected $table = 'spmi_evaluations';

    protected $fillable = ['spmi_realization_id', 'perguruan_tinggi_id', 'program_studi_id', 'result', 'achievement_percentage', 'analysis', 'root_cause', 'recommendation', 'status', 'evaluated_by', 'evaluated_at'];

    protected function casts(): array
    {
        return ['achievement_percentage' => 'decimal:4', 'evaluated_at' => 'datetime'];
    }

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
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
