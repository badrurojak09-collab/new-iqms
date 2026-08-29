<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accreditation extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $fillable = ['perguruan_tinggi_id', 'program_studi_id', 'instrument_version_id', 'code', 'scope_type', 'title', 'status', 'planned_submission_date', 'submitted_at', 'decision_date', 'decision_result', 'owner_id'];

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
    }

    protected function casts(): array
    {
        return ['planned_submission_date' => 'date', 'submitted_at' => 'date', 'decision_date' => 'date'];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function scoreSnapshots(): HasMany
    {
        return $this->hasMany(AccreditationScoreSnapshot::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(AccreditationSection::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AccreditationResponse::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AccreditationSubmission::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AccreditationAssessment::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(AccreditationDecision::class);
    }

    public function readinessItems(): HasMany
    {
        return $this->hasMany(AccreditationReadinessItem::class);
    }
}
