<?php

declare(strict_types=1);

namespace App\Domain\Evidence;

use App\Models\Document;
use App\Models\Evidence;
use App\Models\EvidenceVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class StoreEvidenceLink
{
    public function handle(
        User $actor,
        Evidence $evidence,
        string $url,
        array $metadata = [],
    ): EvidenceVersion {
        if ($evidence->perguruan_tinggi_id !== $actor->perguruan_tinggi_id && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages(['evidence' => 'Evidence berada di luar tenant pengguna.']);
        }

        $url = trim($url);
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages(['external_url' => 'URL dokumen cloud tidak valid.']);
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['https'], true)) {
            throw ValidationException::withMessages(['external_url' => 'URL evidence harus menggunakan HTTPS.']);
        }

        return DB::transaction(function () use ($actor, $evidence, $url, $metadata): EvidenceVersion {
            $versionNo = ((int) $evidence->versions()->max('version_no')) + 1;
            $provider = $this->providerFromUrl($url);
            $externalFileId = $this->extractFileId($url, $provider);
            $manifestHash = hash('sha256', implode('|', [
                $evidence->getKey(),
                $versionNo,
                $url,
                $externalFileId ?? '',
                $metadata['original_name'] ?? '',
                $metadata['sha256'] ?? '',
            ]));

            $document = Document::query()->create([
                'perguruan_tinggi_id' => $evidence->perguruan_tinggi_id,
                'program_studi_id' => $evidence->program_studi_id,
                'uploaded_by' => $actor->getKey(),
                'storage_provider' => $provider,
                'external_url' => $url,
                'external_file_id' => $externalFileId,
                'external_folder_url' => $metadata['external_folder_url'] ?? null,
                'link_access_mode' => $metadata['link_access_mode'] ?? 'institution_managed',
                'disk' => null,
                'storage_path' => null,
                'original_name' => $metadata['original_name'] ?? null,
                'mime_type' => $metadata['mime_type'] ?? null,
                'size_bytes' => isset($metadata['size_bytes']) ? (int) $metadata['size_bytes'] : null,
                'sha256' => $metadata['sha256'] ?? null,
                'visibility' => 'external',
                'status' => 'active',
            ]);

            return $evidence->versions()->create([
                'document_id' => $document->getKey(),
                'created_by' => $actor->getKey(),
                'version_no' => $versionNo,
                'change_reason' => $metadata['change_reason'] ?? null,
                'manifest_hash' => $manifestHash,
            ]);
        });
    }

    private function providerFromUrl(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return match (true) {
            str_contains($host, 'drive.google.com'), str_contains($host, 'docs.google.com') => 'google_drive',
            str_contains($host, 'sharepoint.com'), str_contains($host, 'onedrive.live.com') => 'microsoft_sharepoint',
            str_contains($host, 'dropbox.com') => 'dropbox',
            default => 'institution_cloud',
        };
    }

    private function extractFileId(string $url, string $provider): ?string
    {
        if ($provider !== 'google_drive') {
            return null;
        }

        if (preg_match('~/d/([a-zA-Z0-9_-]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return isset($query['id']) && is_string($query['id']) ? $query['id'] : null;
    }
}
