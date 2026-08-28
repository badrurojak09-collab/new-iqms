<?php

declare(strict_types=1);

namespace App\Domain\Evidence;

use App\Models\Document;
use App\Models\Evidence;
use App\Models\EvidenceVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class StoreEvidenceDocument
{
    public function handle(
        User $actor,
        Evidence $evidence,
        UploadedFile $file,
        ?string $changeReason = null,
    ): EvidenceVersion {
        if ($evidence->perguruan_tinggi_id !== $actor->perguruan_tinggi_id && ! $actor->isSuperAdmin()) {
            throw ValidationException::withMessages(['evidence' => 'Evidence berada di luar tenant pengguna.']);
        }

        if (! $file->isValid()) {
            throw ValidationException::withMessages(['file' => 'File upload tidak valid.']);
        }

        $sha256 = hash_file('sha256', $file->getRealPath());
        if ($sha256 === false) {
            throw ValidationException::withMessages(['file' => 'Integrity hash file gagal dihitung.']);
        }

        return DB::transaction(function () use ($actor, $evidence, $file, $sha256, $changeReason): EvidenceVersion {
            $versionNo = ((int) $evidence->versions()->max('version_no')) + 1;
            $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $path = sprintf('evidence/%d/%s/v%d-%s.%s', $evidence->perguruan_tinggi_id, $evidence->getKey(), $versionNo, $sha256, $extension);
            $disk = 'local';

            Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));

            $document = Document::query()->create([
                'perguruan_tinggi_id' => $evidence->perguruan_tinggi_id,
                'program_studi_id' => $evidence->program_studi_id,
                'uploaded_by' => $actor->getKey(),
                'disk' => $disk,
                'storage_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'sha256' => $sha256,
                'visibility' => 'private',
                'status' => 'active',
            ]);

            return $evidence->versions()->create([
                'document_id' => $document->getKey(),
                'created_by' => $actor->getKey(),
                'version_no' => $versionNo,
                'change_reason' => $changeReason,
                'manifest_hash' => hash('sha256', implode('|', [$evidence->getKey(), $versionNo, $document->getKey(), $sha256])),
            ]);
        });
    }
}
