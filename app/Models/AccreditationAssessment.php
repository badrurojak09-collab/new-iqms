<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationAssessment extends Model
{
    use HasFactory;

    protected $table = 'accreditation_assessments';

    protected $fillable = ['accreditation_id', 'accreditation_response_id', 'assessor_id', 'assessment_type', 'result', 'score', 'notes', 'status', 'assessed_at'];

    protected function casts(): array
    {
        return ['score' => 'decimal:4', 'assessed_at' => 'datetime'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(AccreditationResponse::class, 'accreditation_response_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }
}
