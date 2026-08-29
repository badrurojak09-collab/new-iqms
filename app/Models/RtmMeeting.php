<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class RtmMeeting extends Model
{
    use HasFactory, ScopedByTenant;

    protected $table = 'rtm_meetings';

    protected $fillable = ['perguruan_tinggi_id', 'program_studi_id', 'ami_cycle_id', 'code', 'title', 'held_at', 'status', 'minutes', 'chair_id'];

    protected function casts(): array
    {
        return ['held_at' => 'datetime'];
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

    public function amiCycle(): BelongsTo
    {
        return $this->belongsTo(AmiCycle::class, 'ami_cycle_id');
    }

    public function chair(): BelongsTo
    {
        return $this->belongsTo(User::class, 'chair_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RtmParticipant::class);
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(RtmDecision::class);
    }
}
