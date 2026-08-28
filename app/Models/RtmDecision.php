<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RtmDecision extends Model
{
    use HasFactory;

    protected $table = 'rtm_decisions';

    protected $fillable = ['rtm_meeting_id', 'ami_finding_id', 'readiness_gap_id', 'code', 'decision', 'rationale', 'status'];

    public function readinessGap(): BelongsTo
    {
        return $this->belongsTo(ReadinessGap::class, 'readiness_gap_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(RtmMeeting::class, 'rtm_meeting_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(AmiFinding::class, 'ami_finding_id');
    }
}
