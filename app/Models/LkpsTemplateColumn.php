<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LkpsTemplateColumn extends Model
{
    use HasFactory;

    protected $table = 'lkps_template_columns';

    protected $fillable = ['lkps_template_id', 'column_key', 'label', 'data_type', 'unit', 'is_required', 'min_value', 'max_value', 'decimal_scale', 'allowed_values', 'source_type', 'formula', 'sort_order'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'min_value' => 'decimal:6', 'max_value' => 'decimal:6', 'allowed_values' => 'array', 'formula' => 'array'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LkpsTemplate::class, 'lkps_template_id');
    }
}
