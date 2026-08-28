<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ReadinessGap extends Model
{
    use HasFactory;

    protected $fillable = ['readiness_run_id', 'readiness_result_id', 'gap_type', 'severity', 'item_key', 'description', 'resolution_status', 'resolved_by', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReadinessRun::class, 'readiness_run_id');
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(ReadinessResult::class, 'readiness_result_id');
    }

    public function resolve(User $actor, RtlAction $verifiedAction): self
    {
        if ((int) $verifiedAction->readiness_gap_id !== (int) $this->getKey() || $verifiedAction->status !== 'verified') {
            throw new InvalidArgumentException('Readiness gap hanya dapat di-resolve oleh RTL verified yang terhubung.');
        }

        if ($this->resolution_status !== 'resolved') {
            $this->update([
                'resolution_status' => 'resolved',
                'resolved_by' => $actor->getKey(),
                'resolved_at' => now(),
            ]);
        }

        return $this->refresh();
    }

    public function rtlActions()
    {
        return $this->hasMany(RtlAction::class, 'readiness_gap_id');
    }

    public function rtmDecisions()
    {
        return $this->hasMany(RtmDecision::class, 'readiness_gap_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
