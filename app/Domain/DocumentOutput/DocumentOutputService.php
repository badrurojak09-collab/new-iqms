<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput;

use App\Models\DocumentDefinition;
use App\Models\DocumentGenerationRequest;
use App\Models\DocumentSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DocumentOutputService
{
    public function queue(DocumentDefinition $definition, User $actor, array $scope, array $parameters = [], ?int $templateVersionId = null, ?string $periodLabel = null): DocumentGenerationRequest
    {
        return DocumentGenerationRequest::query()->create([
            'document_definition_id' => $definition->getKey(),
            'document_template_version_id' => $templateVersionId,
            'perguruan_tinggi_id' => $scope['perguruan_tinggi_id'] ?? null,
            'program_studi_id' => $scope['program_studi_id'] ?? null,
            'requested_by' => $actor->getKey(),
            'period_label' => $periodLabel,
            'parameters' => $parameters,
            'status' => 'queued',
        ]);
    }

    public function snapshot(DocumentGenerationRequest $request, array $payload, ?string $sourceContext = null): DocumentSnapshot
    {
        $normalized = json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $encoded = json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return DB::transaction(fn (): DocumentSnapshot => $request->snapshots()->create([
            'payload' => $normalized,
            'payload_hash' => hash('sha256', $encoded),
            'source_context' => $sourceContext,
        ]));
    }
}
