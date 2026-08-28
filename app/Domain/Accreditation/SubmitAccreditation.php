<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;
use App\Models\AccreditationSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitAccreditation
{
    public function handle(Accreditation $accreditation, int $submitterId, ?string $notes = null): AccreditationSubmission
    {
        $accreditation->loadMissing(['instrumentVersion', 'responses', 'readinessItems']);

        if ($accreditation->instrumentVersion->status !== 'published') {
            throw ValidationException::withMessages(['instrument_version' => 'Akreditasi hanya dapat menggunakan instrumen published.']);
        }

        $incompleteReadiness = $accreditation->readinessItems->contains(fn ($item): bool => $item->status !== 'complete');
        if ($accreditation->readinessItems->isNotEmpty() && $incompleteReadiness) {
            throw ValidationException::withMessages(['readiness' => 'Semua readiness item wajib berstatus complete.']);
        }

        if ($accreditation->responses->contains(fn ($response): bool => $response->status !== 'ready')) {
            throw ValidationException::withMessages(['responses' => 'Semua response LED/LKPS wajib berstatus ready.']);
        }

        $submissionNo = ((int) $accreditation->submissions()->max('submission_no')) + 1;
        $manifest = app(BuildSubmissionManifest::class)->handle($accreditation);

        return DB::transaction(function () use ($accreditation, $submitterId, $notes, $submissionNo, $manifest): AccreditationSubmission {
            $submission = $accreditation->submissions()->create([
                'submission_no' => $submissionNo,
                'package_hash' => hash('sha256', json_encode([
                    'manifest' => $manifest,
                ], JSON_THROW_ON_ERROR)),
                'status' => 'submitted',
                'submitted_by' => $submitterId,
                'submitted_at' => now(),
                'notes' => $notes,
            ]);

            $accreditation->forceFill(['status' => 'submitted', 'submitted_at' => now()])->save();

            return $submission;
        });
    }
}
