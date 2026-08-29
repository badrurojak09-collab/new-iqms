<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiFramework extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $table = 'spmi_frameworks';

    protected $fillable = ['perguruan_tinggi_id', 'code', 'name', 'version_label', 'status', 'effective_from', 'effective_until', 'description'];

    protected function casts(): array
    {
        return ['effective_from' => 'date', 'effective_until' => 'date'];
    }

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            // 'program_studi' => 'program_studi_id',
        ];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function standards(): HasMany
    {
        return $this->hasMany(SpmiStandard::class);
    }
}
