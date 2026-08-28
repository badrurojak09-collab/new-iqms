<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccreditationBody extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'kind', 'website', 'status'];

    public function instrumentFamilies(): HasMany
    {
        return $this->hasMany(InstrumentFamily::class);
    }
}
