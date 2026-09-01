<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $table = 'user_profiles';

    protected $fillable = [
        'user_id',
        'nidn',
        'nip',
        'nik',
        'title_prefix',
        'title_suffix',
        'gender',
        'phone',
        'functional_position',
        'structural_position',
        'expertise',
        'address',
        'bio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedFullNameAttribute(): string
    {
        $name = $this->user?->name ?? '';
        $prefix = $this->title_prefix ? trim($this->title_prefix) . ' ' : '';
        $suffix = $this->title_suffix ? ', ' . trim($this->title_suffix) : '';

        return $prefix . $name . $suffix;
    }
}
