<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Yayasan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'yayasan';

    protected $fillable = ['nama', 'kode'];

    protected static function booted(): void
    {
        static::creating(function (self $yayasan): void {
            if (filled($yayasan->kode)) {
                return;
            }

            $yayasan->kode = self::generateNextCode();
        });
    }

    public static function generateNextCode(): string
    {
        $nextNumber = (int) (static::withTrashed()
            ->where('kode', 'like', 'YYS-%')
            ->get()
            ->map(fn(self $yayasan): int => (int) str($yayasan->kode)->after('YYS-')->toString())
            ->max() ?? 0) + 1;

        do {
            $code = 'YYS-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::withTrashed()->where('kode', $code)->exists());

        return $code;
    }

    public function perguruanTinggis(): HasMany
    {
        return $this->hasMany(PerguruanTinggi::class);
    }
}
