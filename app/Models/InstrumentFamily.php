<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstrumentFamily extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['accreditation_body_id', 'code', 'name', 'scope_type', 'description'];

    public function accreditationBody(): BelongsTo
    {
        return $this->belongsTo(AccreditationBody::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InstrumentVersion::class);
    }
}
