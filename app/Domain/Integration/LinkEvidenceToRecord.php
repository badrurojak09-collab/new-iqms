<?php

declare(strict_types=1);

namespace App\Domain\Integration;

use App\Models\AmiFinding;
use App\Models\Evidence;
use App\Models\EvidenceLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final class LinkEvidenceToRecord
{
    /** @var list<string> */
    private const ALLOWED_TYPES = [
        'App\\Models\\AccreditationResponse',
        'App\\Models\\AmiFinding',
        'App\\Models\\SpmiRealization',
        'App\\Models\\RtlAction',
        'App\\Models\\RtlEffectivenessReview',
    ];

    public function handle(Evidence $evidence, Model $record, int $userId, ?string $label = null): EvidenceLink
    {
        if (! in_array($record::class, self::ALLOWED_TYPES, true)) {
            throw ValidationException::withMessages(['record' => 'Tipe record tidak diizinkan menjadi target evidence link.']);
        }

        $recordTenantId = $record->getAttribute('perguruan_tinggi_id');
        if ($recordTenantId === null && $record instanceof AmiFinding) {
            $record->loadMissing('cycle');
            $recordTenantId = $record->cycle?->perguruan_tinggi_id;
        }

        if ((int) $evidence->perguruan_tinggi_id !== (int) $recordTenantId) {
            throw ValidationException::withMessages(['tenant' => 'Evidence dan target record harus berada pada Perguruan Tinggi yang sama.']);
        }

        return EvidenceLink::query()->firstOrCreate([
            'evidence_id' => $evidence->id,
            'linkable_type' => $record::class,
            'linkable_id' => $record->getKey(),
        ], [
            'label' => $label,
            'linked_by' => $userId,
        ]);
    }
}
