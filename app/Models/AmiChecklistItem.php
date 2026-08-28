<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmiChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'ami_checklist_items';

    protected $fillable = ['ami_cycle_id', 'instrument_node_id', 'spmi_standard_id', 'spmi_indicator_id', 'code', 'question', 'response_type', 'response_status', 'score', 'response', 'auditor_notes', 'evidence_required', 'sort_order'];

    protected function casts(): array
    {
        return ['score' => 'decimal:4', 'evidence_required' => 'boolean'];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AmiCycle::class, 'ami_cycle_id');
    }

    public function instrumentNode(): BelongsTo
    {
        return $this->belongsTo(InstrumentNode::class);
    }

    public function spmiStandard(): BelongsTo
    {
        return $this->belongsTo(SpmiStandard::class);
    }

    public function spmiIndicator(): BelongsTo
    {
        return $this->belongsTo(SpmiIndicator::class);
    }
}
