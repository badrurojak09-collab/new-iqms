<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpmiStandard extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $table = 'spmi_standards';

    protected $fillable = ['spmi_framework_id', 'perguruan_tinggi_id', 'program_studi_id', 'code', 'name', 'statement', 'basis', 'status', 'sort_order'];

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            // 'program_studi' => 'program_studi_id',
        ];
    }

    public function framework(): BelongsTo
    {
        return $this->belongsTo(SpmiFramework::class, 'spmi_framework_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(SpmiIndicator::class);
    }
}
