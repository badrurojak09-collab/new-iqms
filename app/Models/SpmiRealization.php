<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class SpmiRealization extends Model
{
    use HasFactory, ScopedByTenant, SoftDeletes;

    protected $table = 'spmi_realizations';

    protected $fillable = ['spmi_target_id', 'spmi_indicator_id', 'perguruan_tinggi_id', 'program_studi_id', 'period_year', 'realization_numeric', 'realization_text', 'source_type', 'source_reference', 'status', 'recorded_by', 'verified_by', 'verified_at', 'verification_notes'];

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
    }

    protected function casts(): array
    {
        return ['realization_numeric' => 'decimal:6', 'period_year' => 'integer', 'verified_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $realization): void {
            if (blank($realization->recorded_by) && Auth::check()) {
                $realization->recorded_by = (int) Auth::id();
            }
        });
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(SpmiTarget::class, 'spmi_target_id');
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(SpmiIndicator::class, 'spmi_indicator_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(SpmiEvaluation::class, 'spmi_realization_id');
    }

    public function evidenceLinks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
