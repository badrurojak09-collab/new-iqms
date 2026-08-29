<?php declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TenantQuery
{
    public static function forYayasan(Builder $query, ?User $user, string $column = 'id'): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $qualifiedColumn = $query->getModel()->qualifyColumn($column);
        $ids = $user->accessibleYayasanIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($qualifiedColumn, $ids);
    }

    public static function forPerguruanTinggi(Builder $query, ?User $user, string $column = 'perguruan_tinggi_id'): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $qualifiedColumn = $query->getModel()->qualifyColumn($column);
        $ids = $user->accessiblePerguruanTinggiIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($qualifiedColumn, $ids);
    }

    public static function forProgramStudi(Builder $query, ?User $user, string $column = 'program_studi_id'): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $qualifiedColumn = $query->getModel()->qualifyColumn($column);
        $ids = $user->accessibleProgramStudiIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($qualifiedColumn, $ids);
    }

    public static function forOptionalProgramStudi(
        Builder $query,
        ?User $user,
        string $ptColumn = 'perguruan_tinggi_id',
        string $prodiColumn = 'program_studi_id'
    ): Builder {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return self::forPerguruanTinggi($query, $user, $ptColumn)
            ->where(function (Builder $nested) use ($user, $prodiColumn): void {
                $nested
                    ->whereNull($nested->getModel()->qualifyColumn($prodiColumn))
                    ->orWhereIn($nested->getModel()->qualifyColumn($prodiColumn), $user->accessibleProgramStudiIds()->values()->all());
            });
    }

    public static function canAccessTenantRecord(?User $user, ?int $perguruanTinggiId, ?int $programStudiId = null): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->accessiblePerguruanTinggiIds()->contains((int) $perguruanTinggiId) &&
            ($programStudiId === null || $user->accessibleProgramStudiIds()->contains((int) $programStudiId));
    }
}
