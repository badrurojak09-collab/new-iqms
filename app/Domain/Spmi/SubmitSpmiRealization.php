<?php declare(strict_types=1);

namespace App\Domain\Spmi;

use App\Models\SpmiRealization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitSpmiRealization
{
    public function handle(SpmiRealization $realization): SpmiRealization
    {
        if (!in_array($realization->status, ['draft', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Hanya realisasi draf atau ditolak yang dapat diajukan.']);
        }

        if (blank($realization->realization_text) && $realization->realization_numeric === null) {
            throw ValidationException::withMessages(['realization_numeric' => 'Isi realisasi numerik atau deskripsi realisasi sebelum mengajukan.']);
        }

        return DB::transaction(function () use ($realization): SpmiRealization {
            $realization->forceFill([
                'status' => 'submitted',
                'verified_by' => null,
                'verified_at' => null,
                'verification_notes' => null,
            ])->save();

            return $realization->refresh();
        });
    }
}
