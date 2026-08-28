<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AccreditationScoreSnapshot extends Model
{
    use HasFactory;

    protected $fillable = ['accreditation_id', 'instrument_version_id', 'calculated_by', 'score', 'status', 'snapshot_hash', 'rule_results', 'input_snapshot', 'calculated_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:4', 'rule_results' => 'array', 'input_snapshot' => 'array', 'calculated_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Score snapshots are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Score snapshots cannot be deleted.'));
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
