<?php declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TenantQuery
{
    public static function forPerguruanTinggi(Builder $query, ?User $user, string $column = 'perguruan_tinggi_id'): Builder
    {
        if ($user === null || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn($query->getModel()->qualifyColumn($column), $user->accessiblePerguruanTinggiIds());
    }

    public static function forProgramStudi(Builder $query, ?User $user, string $column = 'program_studi_id'): Builder
    {
        if ($user === null || $user->isSuperAdmin()) {
            return $query;
        }

        return $query->whereIn($query->getModel()->qualifyColumn($column), $user->accessibleProgramStudiIds());
    }

    public static function forOptionalProgramStudi(Builder $query, ?User $user, string $ptColumn = 'perguruan_tinggi_id', string $prodiColumn = 'program_studi_id'): Builder
    {
        if ($user === null || $user->isSuperAdmin()) {
            return $query;
        }

        return self::forPerguruanTinggi($query, $user, $ptColumn)
            ->where(function (Builder $nested) use ($user, $prodiColumn): void {
                $nested
                    ->whereNull($nested->getModel()->qualifyColumn($prodiColumn))
                    ->orWhereIn($nested->getModel()->qualifyColumn($prodiColumn), $user->accessibleProgramStudiIds());
            });
    }

    public static function canAccessTenantRecord(?User $user, ?int $perguruanTinggiId, ?int $programStudiId = null): bool
    {
        if ($user === null || $user->isSuperAdmin()) {
            return $user !== null;
        }

        return $user->accessiblePerguruanTinggiIds()->contains((int) $perguruanTinggiId) &&
            ($programStudiId === null || $user->accessibleProgramStudiIds()->contains((int) $programStudiId));
    }
}
