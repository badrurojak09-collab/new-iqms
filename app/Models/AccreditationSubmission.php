<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationSubmission extends Model
{
    use HasFactory;

    protected $table = 'accreditation_submissions';

    protected $fillable = ['accreditation_id', 'submission_no', 'package_hash', 'status', 'submitted_by', 'submitted_at', 'notes'];

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
