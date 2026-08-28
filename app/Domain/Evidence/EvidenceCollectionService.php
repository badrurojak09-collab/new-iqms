<?php

declare(strict_types=1);

namespace App\Domain\Evidence;

use App\Models\AuditLog;
use App\Models\Evidence;
use App\Models\EvidenceCollectionItem;
use App\Models\EvidenceLinkCheck;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

final class EvidenceCollectionService
{
    public function attachEvidence(User $actor, EvidenceCollectionItem $item, Evidence $evidence): EvidenceCollectionItem
    {
        $collection = $item->collection()->with('perguruanTinggi')->firstOrFail();
        if (! $actor->isSuperAdmin() && $collection->perguruan_tinggi_id !== $actor->perguruan_tinggi_id) {
            throw ValidationException::withMessages(['evidence' => 'Collection berada di luar tenant pengguna.']);
        }
        if ($evidence->perguruan_tinggi_id !== $collection->perguruan_tinggi_id) {
            throw ValidationException::withMessages(['evidence' => 'Evidence harus berada pada tenant yang sama.']);
        }
        if (! $evidence->versions()->whereHas('document', fn ($query) => $query->whereNotNull('external_url'))->exists()) {
            throw ValidationException::withMessages(['evidence' => 'Evidence belum memiliki versi cloud link.']);
        }

        return DB::transaction(function () use ($actor, $item, $evidence): EvidenceCollectionItem {
            $old = $item->only(['evidence_id', 'status']);
            $item->update(['evidence_id' => $evidence->getKey(), 'status' => 'linked']);
            $this->audit($actor, 'evidence_collection.item_attached', $item, $old, ['evidence_id' => $evidence->getKey(), 'status' => 'linked']);

            return $item->refresh();
        });
    }

    public function checkLatestLink(User $actor, Evidence $evidence): EvidenceLinkCheck
    {
        if (! $actor->isSuperAdmin() && $evidence->perguruan_tinggi_id !== $actor->perguruan_tinggi_id) {
            throw ValidationException::withMessages(['evidence' => 'Evidence berada di luar tenant pengguna.']);
        }
        $version = $evidence->versions()->with('document')->latest('version_no')->first();
        $url = $version?->document?->external_url;
        if (! is_string($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages(['external_url' => 'Evidence belum memiliki URL cloud yang valid.']);
        }

        $status = 'reachable';
        $httpStatus = null;
        $notes = null;
        try {
            $response = Http::timeout(10)->withHeaders(['Accept' => '*/*'])->head($url);
            $httpStatus = $response->status();
            if ($response->failed()) {
                $status = 'unreachable';
                $notes = 'HTTP response '.$httpStatus;
            }
        } catch (ConnectionException $exception) {
            $status = 'unknown';
            $notes = $exception->getMessage();
        }

        $check = EvidenceLinkCheck::query()->create([
            'evidence_id' => $evidence->getKey(),
            'evidence_version_id' => $version?->getKey(),
            'checked_by' => $actor->getKey(),
            'status' => $status,
            'http_status' => $httpStatus,
            'url_hash' => hash('sha256', $url),
            'notes' => $notes,
            'checked_at' => now(),
        ]);
        $version?->document?->update(['last_link_checked_at' => now()]);
        $this->audit($actor, 'evidence.link_checked', $evidence, [], ['status' => $status, 'http_status' => $httpStatus, 'evidence_link_check_id' => $check->getKey()]);

        return $check;
    }

    /** @param array<string, mixed> $old @param array<string, mixed> $new */
    private function audit(User $actor, string $event, object $record, array $old, array $new): void
    {
        AuditLog::query()->create([
            'user_id' => $actor->getKey(),
            'event' => $event,
            'auditable_type' => $record::class,
            'auditable_id' => $record->getKey(),
            'route' => request()->path(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $old,
            'new_values' => $new,
            'context' => ['perguruan_tinggi_id' => $actor->perguruan_tinggi_id ?? null],
        ]);
    }
}
