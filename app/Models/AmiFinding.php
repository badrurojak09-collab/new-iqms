<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmiFinding extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ami_findings';

    protected $fillable = ['ami_cycle_id', 'ami_checklist_item_id', 'reported_by', 'code', 'classification', 'severity', 'requirement', 'condition', 'criteria', 'cause', 'impact', 'recommendation', 'status'];

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(AmiCycle::class, 'ami_cycle_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(AmiChecklistItem::class, 'ami_checklist_item_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function evidenceLinks(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(EvidenceLink::class, 'linkable');
    }
}
