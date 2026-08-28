<?php

declare(strict_types=1);

namespace App\Domain\DocumentOutput\Contracts;

use App\Models\DocumentArtifact;
use App\Models\DocumentGenerationRequest;
use App\Models\DocumentSnapshot;

interface DocumentRenderer
{
    public function format(): string;

    /** @return array{file_name:string, mime_type:string, contents:string} */
    public function render(DocumentGenerationRequest $request, DocumentSnapshot $snapshot): array;
}
