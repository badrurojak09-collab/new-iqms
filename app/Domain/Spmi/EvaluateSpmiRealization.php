<?php

declare(strict_types=1);

namespace App\Domain\Spmi;

use App\Models\SpmiEvaluation;
use App\Models\SpmiRealization;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EvaluateSpmiRealization
{
    public function handle(SpmiRealization $realization, int $evaluatorId, string $analysis, ?string $rootCause = null, ?string $recommendation = null): SpmiEvaluation
    {
        if ($realization->status !== 'verified') {
            throw ValidationException::withMessages(['realization' => 'Realisasi harus diverifikasi sebelum dievaluasi.']);
        }

        $realization->loadMissing('target');
        $target = $realization->target;
        if ($realization->realization_numeric === null || $target->target_numeric === null || (float) $target->target_numeric === 0.0) {
            throw ValidationException::withMessages(['realization_numeric' => 'Realisasi dan target numerik wajib tersedia serta target tidak boleh nol.']);
        }

        $achievement = round(((float) $realization->realization_numeric / (float) $target->target_numeric) * 100, 4);
        $result = $achievement >= 100 ? 'met' : ($achievement >= 80 ? 'partially_met' : 'not_met');

        return DB::transaction(fn (): SpmiEvaluation => SpmiEvaluation::query()->updateOrCreate(
            ['spmi_realization_id' => $realization->getKey()],
            [
                'perguruan_tinggi_id' => $realization->perguruan_tinggi_id,
                'program_studi_id' => $realization->program_studi_id,
                'result' => $result,
                'achievement_percentage' => $achievement,
                'analysis' => $analysis,
                'root_cause' => $rootCause,
                'recommendation' => $recommendation,
                'status' => 'completed',
                'evaluated_by' => $evaluatorId,
                'evaluated_at' => now(),
            ],
        ));
    }
}
