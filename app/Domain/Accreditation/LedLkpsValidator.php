<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;
use App\Models\AccreditationResponse;
use App\Models\EvidenceLink;
use App\Models\InstrumentMapping;
use App\Models\LedTemplate;
use App\Models\LkpsTemplate;

final class LedLkpsValidator
{
    /** @return array{valid:bool, errors:array<int, array<string, mixed>>, summary:array<string, int>} */
    public function validate(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['sections.instrumentNode', 'responses.section', 'responses.instrumentNode']);
        $errors = [];
        $requiredSections = $accreditation->sections->where('status', '!=', 'completed')->count();
        $responsesChecked = 0;

        foreach ($accreditation->sections as $section) {
            if ((int) $section->accreditation_id !== (int) $accreditation->getKey()) {
                $errors[] = $this->error('section', $section->code, 'Section tidak terhubung ke accreditation yang benar.');
            }
            if ($section->instrument_node_id !== null && $section->instrumentNode?->instrument_version_id !== $accreditation->instrument_version_id) {
                $errors[] = $this->error('section', $section->code, 'Instrument node section berbeda versi dengan accreditation.');
            }
            if ($section->status !== 'completed' && (bool) ($section->is_required ?? false)) {
                $errors[] = $this->error('section', $section->code, 'Section wajib belum berstatus completed.');
            }
        }

        foreach ($accreditation->responses as $response) {
            $responsesChecked++;
            $errors = [...$errors, ...$this->validateResponse($response, $accreditation)];
        }
        $errors = [...$errors, ...$this->validateTemplates($accreditation)];

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'summary' => ['sections_pending' => $requiredSections, 'responses_checked' => $responsesChecked, 'errors' => count($errors)],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function validateTemplates(Accreditation $accreditation): array
    {
        $versionId = (int) $accreditation->instrument_version_id;
        $errors = [];
        $ledTemplates = LedTemplate::query()->where('instrument_version_id', $versionId)->with('sections.instrumentNode')->get();
        foreach ($ledTemplates as $template) {
            foreach ($template->sections as $section) {
                if ($section->instrumentNode?->instrument_version_id !== null && (int) $section->instrumentNode->instrument_version_id !== $versionId) {
                    $errors[] = $this->error('led_template', $section->code, 'LED section menggunakan instrument node berbeda versi.');
                }
                if ($section->is_required && ! $accreditation->responses->contains(fn (AccreditationResponse $response): bool => $response->response_key === $section->code && $response->status !== 'rejected')) {
                    $errors[] = $this->error('led_required', $section->code, 'LED section wajib belum memiliki response.');
                }
            }
        }
        $lkpsTemplates = LkpsTemplate::query()->where('instrument_version_id', $versionId)->with('columns')->get();
        foreach ($lkpsTemplates as $template) {
            foreach ($template->columns as $column) {
                if (! $column->is_required) {
                    continue;
                }
                $response = $accreditation->responses->first(fn (AccreditationResponse $item): bool => $item->response_key === $column->column_key);
                $payload = $response?->response_json;
                if (! is_array($payload) || ! array_key_exists($column->column_key, $payload)) {
                    $errors[] = $this->error('lkps_required', $column->column_key, 'Kolom LKPS wajib belum memiliki nilai.');

                    continue;
                }
                $value = $payload[$column->column_key];
                if ($column->data_type === 'numeric' && ! is_numeric($value)) {
                    $errors[] = $this->error('lkps_type', $column->column_key, 'Nilai kolom LKPS harus numeric.');
                }
                if (is_numeric($value) && $column->min_value !== null && (float) $value < (float) $column->min_value) {
                    $errors[] = $this->error('lkps_range', $column->column_key, 'Nilai kolom LKPS di bawah minimum.');
                }
                if (is_numeric($value) && $column->max_value !== null && (float) $value > (float) $column->max_value) {
                    $errors[] = $this->error('lkps_range', $column->column_key, 'Nilai kolom LKPS di atas maksimum.');
                }
                if (is_array($column->allowed_values) && $column->allowed_values !== [] && ! in_array($value, $column->allowed_values, true)) {
                    $errors[] = $this->error('lkps_enum', $column->column_key, 'Nilai kolom LKPS tidak termasuk allowed values.');
                }
            }
        }
        $invalidMappings = InstrumentMapping::query()->where('instrument_version_id', $versionId)->with(['instrumentNode', 'criterion'])->get()->filter(fn (InstrumentMapping $mapping): bool => (int) $mapping->instrumentNode?->instrument_version_id !== $versionId || (int) $mapping->criterion?->instrument_version_id !== $versionId);
        foreach ($invalidMappings as $mapping) {
            $errors[] = $this->error('mapping', (string) $mapping->getKey(), 'Instrument mapping memiliki node atau criterion berbeda versi.');
        }

        return $errors;
    }

    /** @return array<int, array<string, mixed>> */
    private function validateResponse(AccreditationResponse $response, Accreditation $accreditation): array
    {
        $errors = [];
        if ((int) $response->accreditation_id !== (int) $accreditation->getKey()) {
            $errors[] = $this->error('response', $response->response_key, 'Response tidak terhubung ke accreditation yang benar.');
        }
        if ($response->section?->accreditation_id !== $accreditation->getKey()) {
            $errors[] = $this->error('response', $response->response_key, 'Section response berbeda dari accreditation.');
        }
        if ($response->instrumentNode?->instrument_version_id !== null && $response->instrumentNode->instrument_version_id !== $accreditation->instrument_version_id) {
            $errors[] = $this->error('response', $response->response_key, 'Instrument node response berbeda versi.');
        }
        $requiredEvidenceLinks = EvidenceLink::query()->where('linkable_type', AccreditationResponse::class)->where('linkable_id', $response->getKey())->where('is_required', true)->with('evidence')->get();
        if ($requiredEvidenceLinks->isNotEmpty() && ! $requiredEvidenceLinks->contains(fn (EvidenceLink $link): bool => $link->evidence?->status === 'verified')) {
            $errors[] = $this->error('evidence', $response->response_key, 'Response memiliki evidence wajib yang belum accepted/verified.');
        }
        $value = $response->response_type === 'numeric' ? $response->response_numeric : ($response->response_type === 'json' ? $response->response_json : $response->response_text);
        if ($response->status !== 'rejected' && ($value === null || $value === '' || $value === [])) {
            $errors[] = $this->error('response', $response->response_key, 'Response wajib memiliki nilai.');
        }
        if ($response->response_type === 'numeric' && $response->response_numeric !== null && ! is_numeric($response->response_numeric)) {
            $errors[] = $this->error('response', $response->response_key, 'Response numeric tidak valid.');
        }

        return $errors;
    }

    /** @return array{scope:string,key:string,message:string} */
    private function error(string $scope, string $key, string $message): array
    {
        return ['scope' => $scope, 'key' => $key, 'message' => $message];
    }
}
