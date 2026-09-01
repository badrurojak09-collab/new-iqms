<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

final class ResolveTenantContext
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $tenantId = $this->positiveInteger($request->header('X-Tenant-Id'))
                ?? $this->positiveInteger($request->input('tenant_id'));
            $programStudiId = $this->positiveInteger($request->header('X-Program-Studi-Id'))
                ?? $this->positiveInteger($request->input('program_studi_id'));

            $this->tenantContext->set($user, $tenantId, $programStudiId);
        }

        try {
            return $next($request);
        } finally {
            $this->tenantContext->clear();
        }
    }

    private function positiveInteger(mixed $value): ?int
    {
        if ($value === null || filter_var($value, FILTER_VALIDATE_INT) === false) {
            return null;
        }

        $integer = (int) $value;

        return $integer > 0 ? $integer : null;
    }
}
