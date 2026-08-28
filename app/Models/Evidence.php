<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evidence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evidences';

    protected $fillable = [
        'perguruan_tinggi_id', 'program_studi_id', 'created_by', 'code', 'title',
        'description', 'valid_from', 'valid_until', 'status', 'verified_by', 'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'verified_at' => 'datetime',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(EvidenceVersion::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(EvidenceLink::class);
    }

    public function linkChecks(): HasMany
    {
        return $this->hasMany(EvidenceLinkCheck::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(EvidenceReview::class);
    }
}
