<?php

declare(strict_types=1);

namespace App\Domain\Accreditation;

use App\Models\Accreditation;

final class CalculateReadiness
{
    /** @return array{total:int, complete:int, percent:float, ready:bool} */
    public function handle(Accreditation $accreditation): array
    {
        $accreditation->loadMissing(['responses', 'readinessItems']);
        $items = collect();
        $items = $items->merge($accreditation->responses->map(fn ($response): array => ['key' => $response->response_key, 'status' => $response->status]));
        $items = $items->merge($accreditation->readinessItems->map(fn ($item): array => ['key' => $item->item_key, 'status' => $item->status]));
        $total = $items->count();
        $complete = $items->filter(fn (array $item): bool => in_array($item['status'], ['ready', 'complete'], true))->count();
        $percent = $total === 0 ? 0.0 : round(($complete / $total) * 100, 4);

        return ['total' => $total, 'complete' => $complete, 'percent' => $percent, 'ready' => $total > 0 && $complete === $total];
    }
}
