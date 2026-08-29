<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmiCycle extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $table = 'ami_cycles';

    protected $fillable = ['perguruan_tinggi_id', 'program_studi_id', 'instrument_version_id', 'code', 'name', 'period_year', 'scope_type', 'status', 'planned_start', 'planned_end', 'actual_start', 'actual_end', 'coordinator_id'];

    protected function casts(): array
    {
        return ['period_year' => 'integer', 'planned_start' => 'date', 'planned_end' => 'date', 'actual_start' => 'date', 'actual_end' => 'date'];
    }

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
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

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AmiAssignment::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(AmiChecklistItem::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AmiFinding::class);
    }

    public function rtmMeetings(): HasMany
    {
        return $this->hasMany(RtmMeeting::class);
    }
}
