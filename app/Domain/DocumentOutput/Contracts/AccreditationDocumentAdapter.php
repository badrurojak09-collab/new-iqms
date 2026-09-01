<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Contracts;

use App\Models\Accreditation;

interface AccreditationDocumentAdapter
{
    public function getAccreditationBodyCode(): string;

    public function getFamilyCode(): string;

    public function getInstrumentTitle(): string;

    /**
     * Build comprehensive LED (Laporan Evaluasi Diri) structured data.
     *
     * @return array<string, mixed>
     */
    public function buildLedData(Accreditation $accreditation): array;

    /**
     * Build LKPS / LKPT quantitative dataset tables.
     *
     * @return array<string, mixed>
     */
    public function buildLkpsData(Accreditation $accreditation): array;

    /**
     * Build score simulation matrix, weights, and qualification status.
     *
     * @return array<string, mixed>
     */
    public function buildScoreSimulationData(Accreditation $accreditation): array;

    /**
     * Build evidence link citation matrix for all criteria.
     *
     * @return array<string, mixed>
     */
    public function buildEvidenceMatrixData(Accreditation $accreditation): array;
}
