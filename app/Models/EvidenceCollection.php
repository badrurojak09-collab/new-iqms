<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class EvidenceCollection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['perguruan_tinggi_id', 'program_studi_id', 'accreditation_id', 'created_by', 'code', 'name', 'provider', 'root_folder_url', 'root_folder_id', 'status', 'description', 'last_checked_at'];

    protected static function booted(): void
    {
        static::creating(function (self $collection): void {
            $collection->created_by ??= Auth::id();
        });
    }

    protected function casts(): array
    {
        return ['last_checked_at' => 'datetime'];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function accreditation(): BelongsTo
    {
        return $this->belongsTo(Accreditation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EvidenceCollectionItem::class);
    }
}
