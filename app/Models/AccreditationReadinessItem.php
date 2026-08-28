<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccreditationReadinessItem extends Model
{
    use HasFactory;

    protected $table = 'accreditation_readiness_items';

    protected $fillable = ['accreditation_id', 'item_type', 'item_key', 'status', 'notes', 'checked_at', 'checked_by'];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
