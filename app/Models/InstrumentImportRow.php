<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentImportRow extends Model
{
    use HasFactory;

    protected $fillable = ['instrument_import_batch_id', 'row_number', 'entity_type', 'entity_code', 'payload', 'status', 'errors'];

    protected function casts(): array
    {
        return ['payload' => 'array', 'errors' => 'array'];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InstrumentImportBatch::class, 'instrument_import_batch_id');
    }
}
