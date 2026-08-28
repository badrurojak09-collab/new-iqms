<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EvidenceVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DownloadEvidenceDocumentController
{
    public function __invoke(EvidenceVersion $evidenceVersion): Response|RedirectResponse
    {
        abort_unless(auth()->check(), 401);

        $document = $evidenceVersion->document;
        abort_unless($document !== null, 404);
        abort_unless(auth()->user()->canAccessPerguruanTinggi($document->perguruanTinggi), 403);

        if (is_string($document->external_url) && filter_var($document->external_url, FILTER_VALIDATE_URL)) {
            abort_unless(strtolower((string) parse_url($document->external_url, PHP_URL_SCHEME)) === 'https', 422, 'Link evidence harus menggunakan HTTPS.');

            return redirect()->away($document->external_url);
        }

        if (! is_string($document->disk) || ! is_string($document->storage_path)) {
            throw new NotFoundHttpException('Link atau file evidence tidak tersedia.');
        }

        $disk = Storage::disk($document->disk);
        if (! $disk->exists($document->storage_path)) {
            throw new NotFoundHttpException('File evidence tidak ditemukan.');
        }

        $actualHash = hash('sha256', $disk->get($document->storage_path));
        abort_unless(is_string($document->sha256) && hash_equals($document->sha256, $actualHash), 422, 'Integrity file tidak valid.');

        return $disk->download($document->storage_path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'X-Content-SHA256' => $document->sha256,
        ]);
    }
}
