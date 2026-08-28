<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocumentArtifact;
use App\Models\DocumentGenerationRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DocumentOutputController extends Controller
{
    public function preview(DocumentGenerationRequest $request): Response
    {
        request()->user()->can('view', $request) || abort(403);
        $artifact = $this->artifact($request, 'html');
        abort_unless($artifact->mime_type === 'text/html', 404);
        return response(Storage::disk($artifact->storage_provider ?: 'local')->get($artifact->storage_path))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="' . $artifact->file_name . '"');
    }

    public function download(DocumentGenerationRequest $request): StreamedResponse
    {
        request()->user()->can('view', $request) || abort(403);
        $artifact = $request->artifacts()->whereNotNull('storage_path')->latest('id')->firstOrFail();
        abort_unless($request->status === 'completed', 404);
        abort_unless(Storage::disk($artifact->storage_provider ?: 'local')->exists($artifact->storage_path), 404);
        return Storage::disk($artifact->storage_provider ?: 'local')->download($artifact->storage_path, $artifact->file_name, ['Content-Type' => $artifact->mime_type ?: 'application/octet-stream']);
    }

    private function artifact(DocumentGenerationRequest $request, string $format): DocumentArtifact
    {
        abort_unless($request->status === 'completed', 404);
        $artifact = $request->artifacts()->where('format', $format)->whereNotNull('storage_path')->latest('id')->firstOrFail();
        abort_unless(Storage::disk($artifact->storage_provider ?: 'local')->exists($artifact->storage_path), 404);
        return $artifact;
    }
}
