<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\Yayasan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait TenantAwareGlobalSearch
{
    public static function getGloballySearchableAttributes(): array
    {
        return match (static::getModel()) {
            Yayasan::class => ['nama', 'kode'],
            PerguruanTinggi::class => ['nama_pt', 'kode_pt', 'yayasan.nama'],
            ProgramStudi::class => ['nama_prodi', 'kode_prodi', 'perguruanTinggi.nama_pt'],
            default => [],
        };
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        $query = parent::getGlobalSearchEloquentQuery();
        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return match (static::getModel()) {
            Yayasan::class => $query->whereKey($user->yayasan_id ?? 0),
            PerguruanTinggi::class => $query->where(function (Builder $builder) use ($user): void {
                $builder->whereKey($user->perguruan_tinggi_id ?? 0)
                    ->orWhere(function (Builder $nested) use ($user): void {
                        $nested->whereNotNull('yayasan_id')->where('yayasan_id', $user->yayasan_id ?? 0);
                    });
            }),
            ProgramStudi::class => $query->where(function (Builder $builder) use ($user): void {
                $assigned = $user->programStudis()->pluck('program_studi.id');
                if ($assigned->isNotEmpty()) {
                    $builder->whereIn($builder->getModel()->getTable().'.id', $assigned);

                    return;
                }
                $builder->whereHas('perguruanTinggi', function (Builder $nested) use ($user): void {
                    $nested->where('yayasan_id', $user->yayasan_id ?? 0)
                        ->when($user->perguruan_tinggi_id, fn (Builder $q) => $q->whereKey($user->perguruan_tinggi_id));
                });
            }),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return match ($record::class) {
            Yayasan::class => ['Kode' => $record->kode],
            PerguruanTinggi::class => ['Kode' => $record->kode_pt, 'Yayasan' => $record->yayasan?->nama],
            ProgramStudi::class => ['Kode' => $record->kode_prodi, 'PT' => $record->perguruanTinggi?->nama_pt],
            default => [],
        };
    }
}
