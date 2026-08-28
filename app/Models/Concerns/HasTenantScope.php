<?php declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Builder;

trait HasTenantScope
{
    public function isSuperAdmin(): bool
    {
        return method_exists($this, 'hasRole') && $this->hasRole('super_admin');
    }

    public function canAccessPerguruanTinggi(PerguruanTinggi|int|null $perguruanTinggi): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $id = $perguruanTinggi instanceof PerguruanTinggi
            ? $perguruanTinggi->getKey()
            : $perguruanTinggi;

        return $this->perguruan_tinggi_id === $id ||
            ($this->yayasan_id !== null && PerguruanTinggi::query()
                ->whereKey($id)
                ->where('yayasan_id', $this->yayasan_id)
                ->exists());
    }

    public function canAccessProgramStudi(ProgramStudi|int $programStudi): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $id = $programStudi instanceof ProgramStudi ? $programStudi->getKey() : $programStudi;

        $assignedProgramStudiIds = $this->programStudis()->pluck('program_studi.id');

        if ($assignedProgramStudiIds->isNotEmpty()) {
            return $assignedProgramStudiIds->contains($id);
        }

        return ProgramStudi::query()
            ->whereKey($id)
            ->whereHas('perguruanTinggi', fn(Builder $query) => $query
                ->where('yayasan_id', $this->yayasan_id)
                ->when($this->perguruan_tinggi_id, fn(Builder $q) => $q->whereKey($this->perguruan_tinggi_id)))
            ->exists();
    }
}
