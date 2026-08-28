<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InstrumentNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'instrument_version_id',
        'parent_id',
        'node_type',
        'code',
        'title',
        'requirement',
        'guidance',
        'weight',
        'sort_order',
        'is_required',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'is_required' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class, 'instrument_version_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }
}
