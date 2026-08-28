<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedTemplateSection extends Model
{
    use HasFactory;

    protected $table = 'led_template_sections';

    protected $fillable = ['led_template_id', 'instrument_node_id', 'code', 'title', 'guidance', 'is_required', 'sort_order', 'validation_rules'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'validation_rules' => 'array'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LedTemplate::class, 'led_template_id');
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }
}
