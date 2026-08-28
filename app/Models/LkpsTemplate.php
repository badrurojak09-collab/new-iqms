<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LkpsTemplate extends Model
{
    use HasFactory;

    protected $table = 'lkps_templates';

    protected $fillable = ['instrument_version_id', 'code', 'name', 'description', 'row_definition', 'validation_rules', 'is_required', 'sort_order'];

    protected function casts(): array
    {
        return ['row_definition' => 'array', 'validation_rules' => 'array', 'is_required' => 'boolean'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(LkpsTemplateColumn::class);
    }
}
