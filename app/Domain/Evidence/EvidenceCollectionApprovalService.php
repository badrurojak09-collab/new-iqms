<?php

declare(strict_types=1);

namespace App\Domain\Evidence;

use App\Models\AuditLog;
use App\Models\EvidenceCollection;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EvidenceCollectionApprovalService
{
    public function approve(User $actor, EvidenceCollection $collection, ?string $notes = null): EvidenceCollection
    {
        $this->assertTenant($actor, $collection);
        if (in_array($collection->status, ['locked', 'archived'], true)) {
            throw ValidationException::withMessages(['status' => 'Collection terkunci atau archived tidak dapat di-approve ulang.']);
        }

        return DB::transaction(function () use ($actor, $collection, $notes): EvidenceCollection {
            $old = $collection->only(['status', 'approved_by', 'approved_at']);
            $collection->update(['status' => 'approved', 'approved_by' => $actor->getKey(), 'approved_at' => now()]);
            $this->audit($actor, 'evidence_collection.approved', $collection, $old, ['status' => 'approved', 'notes' => $notes]);

            return $collection->refresh();
        });
    }

    public function lockForSubmission(User $actor, EvidenceCollection $collection, string $reason): EvidenceCollection
    {
        $this->assertTenant($actor, $collection);
        if ($collection->status !== 'approved') {
            throw ValidationException::withMessages(['status' => 'Collection harus berstatus approved sebelum dikunci.']);
        }
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['lock_reason' => 'Alasan lock wajib diisi.']);
        }

        return DB::transaction(function () use ($actor, $collection, $reason): EvidenceCollection {
            $old = $collection->only(['status', 'locked_by', 'locked_at', 'lock_reason']);
            $collection->update(['status' => 'locked', 'locked_by' => $actor->getKey(), 'locked_at' => now(), 'lock_reason' => $reason, 'submitted_at' => now()]);
            $this->audit($actor, 'evidence_collection.locked_for_submission', $collection, $old, ['status' => 'locked', 'lock_reason' => $reason]);

            return $collection->refresh();
        });
    }

    public function assertEditable(User $actor, EvidenceCollection $collection): void
    {
        $this->assertTenant($actor, $collection);
        if ($collection->status === 'locked') {
            throw ValidationException::withMessages(['status' => 'Collection sudah locked untuk submission dan tidak dapat diubah.']);
        }
    }

    private function assertTenant(User $actor, EvidenceCollection $collection): void
    {
        if (! $actor->isSuperAdmin() && (int) $collection->perguruan_tinggi_id !== (int) ($actor->perguruan_tinggi_id ?? 0)) {
            throw ValidationException::withMessages(['collection' => 'Collection berada di luar tenant pengguna.']);
        }
    }

    /** @param array<string, mixed> $old @param array<string, mixed> $new */
    private function audit(User $actor, string $event, EvidenceCollection $collection, array $old, array $new): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->getKey(),
            'event' => $event,
            'auditable_type' => $collection::class,
            'auditable_id' => $collection->getKey(),
            'route' => request()->path(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $old,
            'new_values' => $new,
            'context' => ['perguruan_tinggi_id' => $collection->perguruan_tinggi_id, 'program_studi_id' => $collection->program_studi_id],
        ]);
    }
}
