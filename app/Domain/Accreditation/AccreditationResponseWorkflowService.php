<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\AccreditationResponse;
use App\Models\AccreditationResponseRevision;
use App\Models\User;
use App\Support\Audit\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class AccreditationResponseWorkflowService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function submit(AccreditationResponse $response, User $actor): AccreditationResponse
    {
        return $this->transition($response, $actor, 'submit', AccreditationResponse::STATUS_SUBMITTED, 'accreditation.response.submitted', [
            'submitted_at' => now(),
        ]);
    }

    public function startReview(AccreditationResponse $response, User $actor): AccreditationResponse
    {
        return $this->transition($response, $actor, 'review', AccreditationResponse::STATUS_IN_REVIEW, 'accreditation.response.review_started', [
            'reviewed_by' => $actor->getKey(),
            'reviewed_at' => now(),
        ]);
    }

    public function requestRevision(AccreditationResponse $response, User $actor, string $notes): AccreditationResponse
    {
        if (blank($notes)) {
            throw new InvalidArgumentException('Catatan revisi wajib diisi.');
        }

        return $this->transition($response, $actor, 'requestRevision', AccreditationResponse::STATUS_REVISION_REQUIRED, 'accreditation.response.revision_requested', [
            'reviewed_by' => $actor->getKey(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ], $notes);
    }

    public function approve(AccreditationResponse $response, User $actor, ?string $notes = null): AccreditationResponse
    {
        return $this->transition($response, $actor, 'approve', AccreditationResponse::STATUS_APPROVED, 'accreditation.response.approved', [
            'approved_by' => $actor->getKey(),
            'approved_at' => now(),
            'review_notes' => $notes ?: $response->review_notes,
        ], $notes);
    }

    public function reject(AccreditationResponse $response, User $actor, string $notes): AccreditationResponse
    {
        if (blank($notes)) {
            throw new InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        return $this->transition($response, $actor, 'reject', AccreditationResponse::STATUS_REVISION_REQUIRED, 'accreditation.response.rejected', [
            'reviewed_by' => $actor->getKey(),
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ], $notes);
    }

    public function lock(AccreditationResponse $response, User $actor): AccreditationResponse
    {
        return $this->transition($response, $actor, 'lock', AccreditationResponse::STATUS_LOCKED, 'accreditation.response.locked', [
            'locked_by' => $actor->getKey(),
            'locked_at' => now(),
        ]);
    }

    public function revise(AccreditationResponse $response, User $actor, array $attributes, ?string $reason = null): AccreditationResponse
    {
        Gate::forUser($actor)->authorize('update', $response);

        return DB::transaction(function () use ($response, $actor, $attributes, $reason): AccreditationResponse {
            $revision = $this->recordRevision($response, $actor, $reason);
            $attributes['revision_no'] = ((int) $revision->revision_no) + 1;
            $attributes['status'] = AccreditationResponse::STATUS_DRAFT;
            $attributes['last_edited_by'] = $actor->getKey();
            $attributes['reviewed_by'] = null;
            $attributes['reviewed_at'] = null;
            $attributes['approved_by'] = null;
            $attributes['approved_at'] = null;
            $attributes['locked_by'] = null;
            $attributes['locked_at'] = null;
            $response->update($attributes);
            $this->auditLogger->record('accreditation.response.revised', $response, [], ['status' => $response->status, 'revision_no' => $response->revision_no, 'reason' => $reason]);
            return $response->refresh();
        });
    }

    private function transition(AccreditationResponse $response, User $actor, string $ability, string $status, string $event, array $attributes = [], ?string $notes = null): AccreditationResponse
    {
        Gate::forUser($actor)->authorize($ability, $response);

        return DB::transaction(function () use ($response, $actor, $status, $event, $attributes, $notes): AccreditationResponse {
            $old = $response->getAttributes();
            $revision = $this->recordRevision($response, $actor, $notes);
            $attributes['revision_no'] = (int) $revision->revision_no;
            $response->fill(array_merge($attributes, ['status' => $status]));
            $response->save();
            $this->auditLogger->record($event, $response, $old, $response->getChanges());
            return $response->refresh();
        });
    }

    private function recordRevision(AccreditationResponse $response, User $actor, ?string $reason): AccreditationResponseRevision
    {
        $nextRevision = ((int) $response->revisions()->max('revision_no')) + 1;

        return $response->revisions()->create([
            'revision_no' => $nextRevision,
            'status' => (string) ($response->status ?: AccreditationResponse::STATUS_DRAFT),
            'response_text' => $response->response_text,
            'response_numeric' => $response->response_numeric,
            'response_json' => $response->response_json,
            'changed_by' => $actor->getKey(),
            'change_reason' => $reason,
            'changed_at' => now(),
        ]);
    }
}
