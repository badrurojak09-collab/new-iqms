<?php

declare(strict_types=1);

namespace App\Domain\InstrumentRegistry;

use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ImportInstrumentVersion
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): InstrumentVersion
    {
        $this->validatePayload($payload);

        return DB::transaction(function () use ($payload): InstrumentVersion {
            $family = InstrumentFamily::query()->findOrFail($payload['instrument_family_id']);
            $version = $family->versions()->create([
                'version_label' => $payload['version_label'],
                'status' => 'draft',
                'source_reference' => $payload['source_reference'] ?? null,
                'effective_from' => $payload['effective_from'] ?? null,
                'effective_until' => $payload['effective_until'] ?? null,
                'changelog' => $payload['changelog'] ?? null,
            ]);

            foreach ($payload['nodes'] as $node) {
                $version->nodes()->create([
                    'parent_id' => null,
                    'node_type' => $node['node_type'],
                    'code' => $node['code'],
                    'title' => $node['title'],
                    'requirement' => $node['requirement'] ?? null,
                    'guidance' => $node['guidance'] ?? null,
                    'weight' => $node['weight'] ?? null,
                    'sort_order' => $node['sort_order'] ?? 0,
                    'is_required' => $node['is_required'] ?? false,
                    'metadata' => $node['metadata'] ?? null,
                ]);
            }

            return $version->load('nodes');
        });
    }

    /** @param array<string, mixed> $payload */
    private function validatePayload(array $payload): void
    {
        $nodes = Arr::get($payload, 'nodes');
        $codes = is_array($nodes) ? array_column($nodes, 'code') : [];

        if (! is_int($payload['instrument_family_id'] ?? null)
            || ! is_string($payload['version_label'] ?? null)
            || $payload['version_label'] === '') {
            throw ValidationException::withMessages([
                'payload' => 'instrument_family_id dan version_label wajib valid.',
            ]);
        }

        if (! is_array($nodes) || $nodes === [] || count($codes) !== count(array_unique($codes))) {
            throw ValidationException::withMessages([
                'nodes' => 'Nodes wajib berupa array tidak kosong dengan code unik.',
            ]);
        }

        foreach ($nodes as $index => $node) {
            if (! is_array($node) || ! is_string($node['code'] ?? null) || ! is_string($node['title'] ?? null)) {
                throw ValidationException::withMessages([
                    "nodes.$index" => 'Setiap node wajib memiliki code dan title.',
                ]);
            }
        }
    }
}
