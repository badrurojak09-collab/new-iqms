<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedTemplate extends Model
{
    use HasFactory;

    protected $table = 'led_templates';

    protected $fillable = ['instrument_version_id', 'code', 'name', 'description', 'validation_rules'];

    protected function casts(): array
    {
        return ['validation_rules' => 'array'];
    }

    public function instrumentVersion(): BelongsTo
    {
        return $this->belongsTo(InstrumentVersion::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LedTemplateSection::class);
    }
}
