<?php declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\Yayasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasTenantScope
{
    public function isSuperAdmin(): bool
    {
        return method_exists($this, 'hasRole') && $this->hasRole('super_admin');
    }

    public function activeTenantScopes()
    {
        return $this
            ->tenantScopes()
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', today());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today());
            });
    }

    private ?Collection $cachedAccessibleYayasanIds = null;

    private ?Collection $cachedAccessiblePerguruanTinggiIds = null;

    private ?Collection $cachedAccessibleProgramStudiIds = null;

    public function flushTenantScopeCache(): void
    {
        $this->cachedAccessibleYayasanIds = null;
        $this->cachedAccessiblePerguruanTinggiIds = null;
        $this->cachedAccessibleProgramStudiIds = null;
    }

    public function accessibleYayasanIds(): Collection
    {
        if ($this->cachedAccessibleYayasanIds !== null) {
            return $this->cachedAccessibleYayasanIds;
        }

        if ($this->isSuperAdmin()) {
            return $this->cachedAccessibleYayasanIds = Yayasan::query()->pluck('id');
        }

        $ids = $this
            ->activeTenantScopes()
            ->where('scope_type', 'yayasan')
            ->pluck('scope_id');

        $ids = $ids->merge(
            PerguruanTinggi::query()
                ->whereIn('id', $this->accessiblePerguruanTinggiIds())
                ->pluck('yayasan_id'),
        );

        if ($this->yayasan_id !== null) {
            $ids->push($this->yayasan_id);
        }

        return $this->cachedAccessibleYayasanIds = $this->normalizeTenantIds($ids);
    }

    public function accessiblePerguruanTinggiIds(): Collection
    {
        if ($this->cachedAccessiblePerguruanTinggiIds !== null) {
            return $this->cachedAccessiblePerguruanTinggiIds;
        }

        if ($this->isSuperAdmin()) {
            return $this->cachedAccessiblePerguruanTinggiIds = PerguruanTinggi::query()->pluck('id');
        }

        $ids = $this
            ->activeTenantScopes()
            ->where('scope_type', 'perguruan_tinggi')
            ->pluck('scope_id');

        $ids = $ids->merge(
            PerguruanTinggi::query()
                ->whereIn('yayasan_id', $this->directYayasanScopeIds())
                ->pluck('id'),
        );

        $ids = $ids->merge(
            ProgramStudi::query()
                ->whereIn('id', $this->directProgramStudiScopeIds())
                ->pluck('perguruan_tinggi_id'),
        );

        if ($this->perguruan_tinggi_id !== null) {
            $ids->push($this->perguruan_tinggi_id);
        }

        return $this->cachedAccessiblePerguruanTinggiIds = $this->normalizeTenantIds($ids);
    }

    public function accessibleProgramStudiIds(): Collection
    {
        if ($this->cachedAccessibleProgramStudiIds !== null) {
            return $this->cachedAccessibleProgramStudiIds;
        }

        if ($this->isSuperAdmin()) {
            return $this->cachedAccessibleProgramStudiIds = ProgramStudi::query()->pluck('id');
        }

        $directAssignments = $this->programStudis()->pluck('program_studi.id');
        if ($directAssignments->isNotEmpty()) {
            return $this->cachedAccessibleProgramStudiIds = $this->normalizeTenantIds($directAssignments);
        }

        $scopes = $this->activeTenantScopes()->get(['scope_type', 'scope_id']);
        $ids = $scopes->where('scope_type', 'program_studi')->pluck('scope_id');
        $ptIds = $scopes->where('scope_type', 'perguruan_tinggi')->pluck('scope_id');
        $yayasanIds = $scopes->where('scope_type', 'yayasan')->pluck('scope_id');

        if ($this->perguruan_tinggi_id !== null) {
            $ptIds->push($this->perguruan_tinggi_id);
        }
        if ($this->yayasan_id !== null) {
            $yayasanIds->push($this->yayasan_id);
        }

        if ($ptIds->isNotEmpty()) {
            $ids = $ids->merge(
                ProgramStudi::query()->whereIn('perguruan_tinggi_id', $ptIds)->pluck('id'),
            );
        }

        if ($yayasanIds->isNotEmpty()) {
            $ids = $ids->merge(
                ProgramStudi::query()
                    ->whereHas('perguruanTinggi', fn(Builder $query) => $query->whereIn('yayasan_id', $yayasanIds))
                    ->pluck('id'),
            );
        }

        return $this->cachedAccessibleProgramStudiIds = $this->normalizeTenantIds($ids);
    }

    public function canAccessYayasan(Yayasan|int|null $yayasan): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($yayasan === null) {
            return false;
        }

        $id = $yayasan instanceof Yayasan ? $yayasan->getKey() : $yayasan;
        return $this->accessibleYayasanIds()->contains((int) $id);
    }

    public function canAccessPerguruanTinggi(PerguruanTinggi|int|null $perguruanTinggi): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($perguruanTinggi === null) {
            return false;
        }

        $id = $perguruanTinggi instanceof PerguruanTinggi
            ? $perguruanTinggi->getKey()
            : $perguruanTinggi;

        return $this->accessiblePerguruanTinggiIds()->contains((int) $id);
    }

    public function canAccessProgramStudi(ProgramStudi|int|null $programStudi): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($programStudi === null) {
            return false;
        }

        $id = $programStudi instanceof ProgramStudi ? $programStudi->getKey() : $programStudi;
        return $this->accessibleProgramStudiIds()->contains((int) $id);
    }

    private function directYayasanScopeIds(): Collection
    {
        $ids = $this->activeTenantScopes()->where('scope_type', 'yayasan')->pluck('scope_id');
        if ($this->yayasan_id !== null) {
            $ids->push($this->yayasan_id);
        }
        return $this->normalizeTenantIds($ids);
    }

    private function directProgramStudiScopeIds(): Collection
    {
        return $this->normalizeTenantIds(
            $this->activeTenantScopes()->where('scope_type', 'program_studi')->pluck('scope_id'),
        );
    }

    private function normalizeTenantIds(Collection $ids): Collection
    {
        return $ids->filter()->map(static fn($id): int => (int) $id)->unique()->values();
    }
}
