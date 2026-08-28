<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event', 'auditable_type', 'auditable_id', 'route', 'ip_address', 'user_agent', 'old_values', 'new_values', 'context'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'context' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
