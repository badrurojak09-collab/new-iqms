<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

class UserTenantScope extends Model
{
    use HasFactory;

    protected $table = 'user_tenant_scopes';

    protected $fillable = [
        'user_id',
        'scope_type',
        'scope_id',
        'role_id',
        'is_default',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function scopeLabel(): string
    {
        return match ($this->scope_type) {
            'yayasan' => (string) Yayasan::query()->whereKey($this->scope_id)->value('nama'),
            'perguruan_tinggi' => (string) PerguruanTinggi::query()->whereKey($this->scope_id)->value('nama_pt'),
            'program_studi' => (string) ProgramStudi::query()->whereKey($this->scope_id)->value('nama_prodi'),
            default => (string) $this->scope_id,
        } ?: 'Lingkup tidak ditemukan';
    }
}
