<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiIndicator extends Model
{
    use HasFactory, ScopedByTenant, SoftDeletes;

    protected $table = 'spmi_indicators';

    protected $fillable = ['spmi_standard_id', 'perguruan_tinggi_id', 'code', 'name', 'definition', 'measurement_type', 'unit', 'weight', 'status', 'validation_rules'];

    protected function casts(): array
    {
        return ['weight' => 'decimal:4', 'validation_rules' => 'array'];
    }

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            // 'program_studi' => 'program_studi_id',
        ];
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
