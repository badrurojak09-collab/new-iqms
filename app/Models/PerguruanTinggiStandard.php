<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerguruanTinggiStandard extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $table = 'perguruan_tinggi_standards';

    protected $fillable = [
        'perguruan_tinggi_id',
        'code',
        'name',
        'category',
        'statement',
        'basis',
        'status',
        'sort_order',
        'effective_from',
        'effective_until',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    protected static function tenantScopeColumns(): array
    {
        return ['perguruan_tinggi' => 'perguruan_tinggi_id'];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function spmiStandards(): HasMany
    {
        return $this->hasMany(SpmiStandard::class, 'perguruan_tinggi_standard_id');
    }
}
