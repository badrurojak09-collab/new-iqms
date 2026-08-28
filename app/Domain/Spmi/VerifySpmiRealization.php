<?php

declare(strict_types=1);

namespace App\Domain\Spmi;

use App\Models\SpmiRealization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class VerifySpmiRealization
{
    public function handle(SpmiRealization $realization, int $verifierId, ?string $notes = null): SpmiRealization
    {
        if (! in_array($realization->status, ['submitted', 'draft'], true)) {
            throw ValidationException::withMessages(['status' => 'Hanya realisasi draft atau submitted yang dapat diverifikasi.']);
        }

        return DB::transaction(function () use ($realization, $verifierId, $notes): SpmiRealization {
            $realization->forceFill([
                'status' => 'verified',
                'verified_by' => $verifierId,
                'verified_at' => now(),
                'verification_notes' => $notes,
            ])->save();

            return $realization->refresh();
        });
    }
}
