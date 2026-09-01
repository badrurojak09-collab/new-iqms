<?php declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TenantQuery
{
    public static function forYayasan(Builder $query, ?User $user, ?string $column = null): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $ids = $user->accessibleYayasanIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $targetColumn = $column ?? ($model instanceof \App\Models\Yayasan || $model->getTable() === 'yayasan' ? $model->getKeyName() : 'yayasan_id');

        return $query->whereIn($model->qualifyColumn($targetColumn), $ids);
    }

    public static function forPerguruanTinggi(Builder $query, ?User $user, ?string $column = null): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $ids = $user->accessiblePerguruanTinggiIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $targetColumn = $column ?? ($model instanceof \App\Models\PerguruanTinggi || $model->getTable() === 'perguruan_tinggi' ? $model->getKeyName() : 'perguruan_tinggi_id');

        return $query->whereIn($model->qualifyColumn($targetColumn), $ids);
    }

    public static function forProgramStudi(Builder $query, ?User $user, ?string $column = null): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $ids = $user->accessibleProgramStudiIds()->values()->all();

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $targetColumn = $column ?? ($model instanceof \App\Models\ProgramStudi || $model->getTable() === 'program_studi' ? $model->getKeyName() : 'program_studi_id');

        return $query->whereIn($model->qualifyColumn($targetColumn), $ids);
    }

    public static function forOptionalProgramStudi(
        Builder $query,
        ?User $user,
        string $ptColumn = 'perguruan_tinggi_id',
        string $prodiColumn = 'program_studi_id',
    ): Builder {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        $ptIds = $user->accessiblePerguruanTinggiIds()->values()->all();
        $prodiIds = $user->accessibleProgramStudiIds()->values()->all();

        if ($ptIds === []) {
            return $query->whereRaw('1 = 0');
        }

        $model = $query->getModel();
        $qualifiedPt = $model->qualifyColumn($ptColumn);
        $qualifiedProdi = $model->qualifyColumn($prodiColumn);

        return $query
            ->whereIn($qualifiedPt, $ptIds)
            ->where(function (Builder $nested) use ($qualifiedProdi, $prodiIds): void {
                $nested->whereNull($qualifiedProdi);
                if ($prodiIds !== []) {
                    $nested->orWhereIn($qualifiedProdi, $prodiIds);
                }
            });
    }

    public static function canAccessTenantRecord(
        ?User $user,
        ?int $perguruanTinggiId,
        ?int $programStudiId = null,
    ): bool {
        if ($user === null) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($perguruanTinggiId === null || !$user->accessiblePerguruanTinggiIds()->contains($perguruanTinggiId)) {
            return false;
        }

        return $programStudiId === null ||
            $user->accessibleProgramStudiIds()->contains($programStudiId);
    }
}
