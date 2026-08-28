<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpmiIndicator extends Model
{
    use HasFactory;

    protected $table = 'spmi_indicators';

    protected $fillable = ['spmi_standard_id', 'perguruan_tinggi_id', 'code', 'name', 'definition', 'measurement_type', 'unit', 'weight', 'status', 'validation_rules'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'validation_rules' => 'array'];
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(SpmiStandard::class, 'spmi_standard_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(SpmiTarget::class);
    }

    public function realizations(): HasMany
    {
        return $this->hasMany(SpmiRealization::class);
    }
}
