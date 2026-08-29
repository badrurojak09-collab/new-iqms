<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SpmiTarget extends Model
{
    use HasFactory, ScopedByTenant;

    protected $table = 'spmi_targets';

    protected $fillable = ['spmi_indicator_id', 'perguruan_tinggi_id', 'program_studi_id', 'period_year', 'period_code', 'target_numeric', 'target_text', 'status', 'set_by'];

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
    }

    protected function casts(): array
    {
        return ['target_numeric' => 'decimal:6', 'period_year' => 'integer'];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(SpmiIndicator::class, 'spmi_indicator_id');
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function setter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
