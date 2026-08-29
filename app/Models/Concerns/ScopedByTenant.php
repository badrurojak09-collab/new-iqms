<?php declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use LogicException;

/**
 * Adds a fail-closed, tenant-aware global scope to operational models.
 *
 * Important:
 * - This trait is intentionally opt-in. Add it only to models whose tenant
 *   columns are declared by overriding tenantScopeColumns().
 * - The global scope never bypasses itself merely because the application is
 *   running from a queue or Artisan command.
 * - Seeders, migrations, and trusted maintenance commands must explicitly use
 *   withoutGlobalScope('sqm_tenant') or withoutGlobalScopes().
 */
trait ScopedByTenant
{
    public const TENANT_SCOPE_NAME = 'sqm_tenant';

    protected static function bootScopedByTenant(): void
    {
        static::addGlobalScope(self::TENANT_SCOPE_NAME, function (Builder $query): void {
            /** @var self $model */
            $model = $query->getModel();
            $user = Auth::user();

            // Only the explicitly privileged application role may see all tenants.
            if ($user instanceof User && $user->isSuperAdmin()) {
                return;
            }

            // Fail closed for unauthenticated web requests, queue jobs, and
            // console commands. Bypass must be explicit at the call site.
            if (!$user instanceof User) {
                $model->denyTenantQuery($query);
                return;
            }

            $columns = $model->tenantScopeColumns();

            if ($columns === []) {
                throw new LogicException(sprintf(
                    '%s uses ScopedByTenant but does not declare tenantScopeColumns().',
                    $model::class,
                ));
            }

            $model->applyTenantScope($query, $user, $columns);
        });
    }

    /**
     * Declare only columns that actually exist on the model's table.
     *
     * Supported keys:
     * - yayasan
     * - perguruan_tinggi
     * - program_studi
     *
     * Example for a model containing both PT and Prodi:
     *
     * protected static function tenantScopeColumns(): array
     * {
     *     return [
     *         'perguruan_tinggi' => 'perguruan_tinggi_id',
     *         'program_studi' => 'program_studi_id',
     *     ];
     * }
     *
     * @return array<string, string>
     */
    protected static function tenantScopeColumns(): array
    {
        return [];
    }

    /**
     * Whether a null program_studi_id represents a valid PT-level record.
     * Override and return false for models that must always belong to a Prodi.
     */
    protected static function tenantAllowsNullProgramStudi(): bool
    {
        return true;
    }

    /**
     * Apply the correct hierarchy without allowing a PT-only filter to bypass
     * a narrower direct Prodi assignment.
     *
     * @param array<string, string> $columns
     */
    protected function applyTenantScope(Builder $query, User $user, array $columns): void
    {
        $model = $query->getModel();

        $yayasanColumn = $columns['yayasan'] ?? null;
        $ptColumn = $columns['perguruan_tinggi'] ?? null;
        $prodiColumn = $columns['program_studi'] ?? null;

        if ($yayasanColumn !== null) {
            $this->whereAccessibleIds(
                $query,
                $model->qualifyColumn($yayasanColumn),
                $user->accessibleYayasanIds()->all(),
            );
        }

        if ($ptColumn !== null && $prodiColumn !== null) {
            $ptQualified = $model->qualifyColumn($ptColumn);
            $prodiQualified = $model->qualifyColumn($prodiColumn);
            $ptIds = $user->accessiblePerguruanTinggiIds()->all();
            $prodiIds = $user->accessibleProgramStudiIds()->all();

            if ($ptIds === [] || $prodiIds === []) {
                $this->denyTenantQuery($query);
                return;
            }

            $query
                ->whereIn($ptQualified, $ptIds)
                ->where(function (Builder $nested) use ($prodiQualified, $prodiIds): void {
                    if (static::tenantAllowsNullProgramStudi()) {
                        $nested->whereNull($prodiQualified);
                    }

                    $nested->orWhereIn($prodiQualified, $prodiIds);
                });

            return;
        }

        if ($ptColumn !== null) {
            $this->whereAccessibleIds(
                $query,
                $model->qualifyColumn($ptColumn),
                $user->accessiblePerguruanTinggiIds()->all(),
            );

            return;
        }

        if ($prodiColumn !== null) {
            $this->whereAccessibleIds(
                $query,
                $model->qualifyColumn($prodiColumn),
                $user->accessibleProgramStudiIds()->all(),
            );

            return;
        }

        // A trait-bearing model without a recognized tenant key must never
        // become globally readable by accident.
        $this->denyTenantQuery($query);
    }

    /**
     * Apply an IN filter, but turn an empty assignment into an explicit deny.
     *
     * @param array<int, int|string> $ids
     */
    protected function whereAccessibleIds(Builder $query, string $qualifiedColumn, array $ids): void
    {
        if ($ids === []) {
            $this->denyTenantQuery($query);
            return;
        }

        $query->whereIn($qualifiedColumn, $ids);
    }

    protected function denyTenantQuery(Builder $query): void
    {
        $query->whereRaw('1 = 0');
    }

    /**
     * Explicit escape hatch for trusted maintenance code only.
     *
     * Example:
     *
     * Accreditation::withoutGlobalScope('sqm_tenant')->get();
     */
    public static function withoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(self::TENANT_SCOPE_NAME);
    }
}
