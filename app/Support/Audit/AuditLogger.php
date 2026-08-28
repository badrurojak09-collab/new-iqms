<?php

declare(strict_types=1);

namespace App\Support\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

final class AuditLogger
{
    public function record(string $event, ?Model $auditable = null, array $old = [], array $new = [], array $context = []): AuditLog
    {
        $request = app()->bound('request') ? app(Request::class) : null;

        return AuditLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'route' => $request?->route()?->getName(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'old_values' => $this->sanitize($old),
            'new_values' => $this->sanitize($new),
            'context' => $this->sanitize($context),
        ]);
    }

    private function sanitize(array $values): array
    {
        foreach (['password', 'password_confirmation', 'remember_token', 'api_token'] as $field) {
            unset($values[$field]);
        }

        return $values;
    }
}
