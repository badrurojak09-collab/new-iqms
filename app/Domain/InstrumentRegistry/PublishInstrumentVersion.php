<?php

declare(strict_types=1);

namespace App\Domain\InstrumentRegistry;

use App\Models\InstrumentVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class PublishInstrumentVersion
{
    public function handle(InstrumentVersion $version, int $publisherId): InstrumentVersion
    {
        if ($version->isImmutable()) {
            throw new RuntimeException('Instrument version sudah immutable.');
        }

        if (! in_array($version->status, ['draft', 'review'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Hanya version draft atau review yang dapat dipublish.',
            ]);
        }

        $version->loadMissing([
            'family',
            'nodes',
            'assessmentCriteria.elements.indicators',
            'assessmentScales.options',
            'assessmentRubrics',
        ]);

        if ($version->nodes->isEmpty()) {
            throw ValidationException::withMessages([
                'nodes' => 'Instrument version minimal harus memiliki satu node.',
            ]);
        }

        $manifest = [
            'nodes' => $version->nodes->sortBy('code')->map(fn ($node): array => [
                'code' => $node->code,
                'node_type' => $node->node_type,
                'title' => $node->title,
                'requirement' => $node->requirement,
                'weight' => (string) $node->weight,
                'is_required' => $node->is_required,
                'metadata' => $node->metadata,
            ])->values()->all(),
            'criteria' => $version->assessmentCriteria->sortBy('code')->map(fn ($criterion): array => [
                'code' => $criterion->code,
                'name' => $criterion->name,
                'weight' => (string) $criterion->weight,
                'elements' => $criterion->elements->sortBy('code')->map(fn ($element): array => [
                    'code' => $element->code,
                    'title' => $element->title,
                    'type' => $element->element_type,
                    'weight' => (string) $element->weight,
                    'indicators' => $element->indicators->sortBy('code')->map(fn ($indicator): array => [
                        'code' => $indicator->code,
                        'name' => $indicator->name,
                        'unit' => $indicator->unit,
                        'data_type' => $indicator->data_type,
                        'direction' => $indicator->direction,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
            'scales' => $version->assessmentScales->sortBy('code')->map(fn ($scale): array => [
                'code' => $scale->code,
                'name' => $scale->name,
                'scale_type' => $scale->scale_type,
                'options' => $scale->options->sortBy('code')->map(fn ($option): array => [
                    'code' => $option->code,
                    'label' => $option->label,
                    'numeric_value' => (string) $option->numeric_value,
                ])->values()->all(),
            ])->values()->all(),
            'rubrics' => $version->assessmentRubrics->sortBy('id')->map(fn ($rubric): array => [
                'label' => $rubric->label,
                'min_score' => (string) $rubric->min_score,
                'max_score' => (string) $rubric->max_score,
                'description' => $rubric->description,
            ])->values()->all(),
        ];

        return DB::transaction(function () use ($version, $publisherId, $manifest): InstrumentVersion {
            $version->forceFill([
                'status' => 'published',
                'content_hash' => hash('sha256', json_encode($manifest, JSON_THROW_ON_ERROR)),
                'published_at' => now(),
                'published_by' => $publisherId,
            ])->save();

            return $version->refresh();
        });
    }
}
