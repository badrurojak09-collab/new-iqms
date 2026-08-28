<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentScoringRule extends Model
{
    use HasFactory;

    protected $table = 'instrument_scoring_rules';

    protected $fillable = ['instrument_version_id', 'code', 'rule_type', 'expression', 'parameters'];

    protected function casts(): array
    {
        return ['expression' => 'array', 'parameters' => 'array'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }
}
