<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class AccreditationResponseRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'accreditation_response_id', 'revision_no', 'status',
        'response_text', 'response_numeric', 'response_json',
        'changed_by', 'change_reason', 'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'response_numeric' => 'decimal:6',
            'response_json' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(AccreditationResponse::class, 'accreditation_response_id');
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
