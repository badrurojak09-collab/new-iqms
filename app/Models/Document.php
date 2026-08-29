<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ScopedByTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, ScopedByTenant;

    protected $fillable = [
        'perguruan_tinggi_id',
        'program_studi_id',
        'uploaded_by',
        'storage_provider',
        'external_url',
        'external_file_id',
        'external_folder_url',
        'link_access_mode',
        'disk',
        'storage_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'sha256',
        'visibility',
        'status',
        'last_link_checked_at',
    ];

    protected static function tenantScopeColumns(): array
    {
        return [
            'perguruan_tinggi' => 'perguruan_tinggi_id',
            'program_studi' => 'program_studi_id',
        ];
    }

    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'last_link_checked_at' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function evidenceVersions(): HasMany
    {
        return $this->hasMany(EvidenceVersion::class);
    }
}
