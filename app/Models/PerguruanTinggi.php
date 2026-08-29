<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerguruanTinggi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'perguruan_tinggi';

    protected $fillable = ['yayasan_id', 'nama_pt', 'kode_pt', 'jenis', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $perguruanTinggi): void {
            if (filled($perguruanTinggi->kode_pt)) {
                return;
            }

            $perguruanTinggi->kode_pt = self::generateNextCode();
        });
    }

    public static function generateNextCode(): string
    {
        $nextNumber = (int) (static::withTrashed()
            ->where('kode_pt', 'like', 'PT-%')
            ->get()
            ->map(fn(self $pt): int => (int) str($pt->kode_pt)->after('PT-')->toString())
            ->max() ?? 0) + 1;

        do {
            $code = 'PT-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::withTrashed()->where('kode_pt', $code)->exists());

        return $code;
    }

    public function yayasan(): BelongsTo
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function programStudis(): HasMany
    {
        return $this->hasMany(ProgramStudi::class);
    }
}
