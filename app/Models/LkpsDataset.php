<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LkpsDataset extends Model
{
    use HasFactory;

    protected $table = 'lkps_datasets';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_LOCKED = 'locked';

    protected $fillable = [
        'accreditation_id',
        'lkps_template_id',
        'status',
        'rows_data',
        'summary_metrics',
        'validation_errors',
        'last_edited_by',
        'approved_by',
        'approved_at',
        'locked_by',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'rows_data' => 'array',
            'summary_metrics' => 'array',
            'validation_errors' => 'array',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LkpsTemplate::class, 'lkps_template_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function locker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        return $this->status === self::STATUS_LOCKED || $this->locked_at !== null;
    }
}
