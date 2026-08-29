<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramStudi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program_studi';

    protected $fillable = ['perguruan_tinggi_id', 'nama_prodi', 'kode_prodi', 'jenjang', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $programStudi): void {
            if (filled($programStudi->kode_prodi)) {
                return;
            }

            $programStudi->kode_prodi = self::generateNextCode();
        });
    }

    public static function generateNextCode(): string
    {
        $nextNumber = (int) (static::withTrashed()
            ->where('kode_prodi', 'like', 'PRD-%')
            ->get()
            ->map(fn(self $prodi): int => (int) str($prodi->kode_prodi)->after('PRD-')->toString())
            ->max() ?? 0) + 1;

        do {
            $code = 'PRD-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::withTrashed()->where('kode_prodi', $code)->exists());

        return $code;
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function users(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'user_program_studi')
            ->withPivot(['peran', 'starts_at', 'ends_at'])
            ->withTimestamps();
    }
}
