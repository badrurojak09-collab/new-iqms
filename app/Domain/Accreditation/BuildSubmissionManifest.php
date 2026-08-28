<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;

final class BuildSubmissionManifest
{
    /** @return array<string, mixed> */
    public function handle(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['instrumentVersion', 'sections', 'responses', 'readinessItems']);

        return [
            'aggregate_id' => $accreditation->getKey(),
            'scope_type' => $accreditation->scope_type,
            'perguruan_tinggi_id' => $accreditation->perguruan_tinggi_id,
            'program_studi_id' => $accreditation->program_studi_id,
            'instrument_version_id' => $accreditation->instrument_version_id,
            'instrument_hash' => $accreditation->instrumentVersion->content_hash,
            'sections' => $accreditation->sections->sortBy('code')->map(fn ($section): array => ['code' => $section->code, 'type' => $section->section_type])->values()->all(),
            'responses' => $accreditation->responses->sortBy('response_key')->map(fn ($response): array => [
                'key' => $response->response_key,
                'type' => $response->response_type,
                'text' => $response->response_text,
                'numeric' => (string) $response->response_numeric,
                'json' => $response->response_json,
            ])->values()->all(),
            'readiness' => $accreditation->readinessItems->sortBy('item_key')->map(fn ($item): array => ['type' => $item->item_type, 'key' => $item->item_key, 'status' => $item->status])->values()->all(),
        ];
    }

    public function hash(Accreditation $accreditation): string
    {
        return hash('sha256', json_encode($this->handle($accreditation), JSON_THROW_ON_ERROR));
    }
}
