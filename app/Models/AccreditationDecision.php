<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationDecision extends Model
{
    use HasFactory;

    protected $table = 'accreditation_decisions';

    protected $fillable = ['accreditation_id', 'decision_type', 'result', 'notes', 'decision_date', 'valid_until', 'decided_by'];

    protected function casts(): array
    {
        return ['decision_date' => 'date', 'valid_until' => 'date'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
