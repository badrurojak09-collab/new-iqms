<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class TenantContext
{
    private ?Yayasan $yayasan = null;

    private ?PerguruanTinggi $perguruanTinggi = null;

    private ?ProgramStudi $programStudi = null;

    public function set(User $user, ?int $perguruanTinggiId = null, ?int $programStudiId = null): self
    {
        if ($perguruanTinggiId !== null) {
            $perguruanTinggi = PerguruanTinggi::query()->findOrFail($perguruanTinggiId);

            if (! $user->canAccessPerguruanTinggi($perguruanTinggi)) {
                throw new AccessDeniedHttpException('Tenant tidak dapat diakses oleh pengguna ini.');
            }

            $this->perguruanTinggi = $perguruanTinggi;
            $this->yayasan = $perguruanTinggi->yayasan;
        } elseif ($user->perguruan_tinggi_id !== null) {
            $this->perguruanTinggi = $user->perguruanTinggi;
            $this->yayasan = $user->yayasan;
        } elseif ($user->yayasan_id !== null) {
            $this->yayasan = $user->yayasan;
        }

        if ($programStudiId !== null) {
            $programStudi = ProgramStudi::query()->with('perguruanTinggi')->findOrFail($programStudiId);

            if (! $user->canAccessProgramStudi($programStudi)) {
                throw new AccessDeniedHttpException('Program studi tidak dapat diakses oleh pengguna ini.');
            }

            if ($this->perguruanTinggi !== null && $programStudi->perguruan_tinggi_id !== $this->perguruanTinggi->getKey()) {
                throw new AccessDeniedHttpException('Program studi berada di luar tenant aktif.');
            }

            $this->programStudi = $programStudi;
            $this->perguruanTinggi ??= $programStudi->perguruanTinggi;
            $this->yayasan ??= $programStudi->perguruanTinggi->yayasan;
        }

        return $this;
    }

    public function yayasan(): ?Yayasan
    {
        return $this->yayasan;
    }

    public function perguruanTinggi(): ?PerguruanTinggi
    {
        return $this->perguruanTinggi;
    }

    public function programStudi(): ?ProgramStudi
    {
        return $this->programStudi;
    }

    public function perguruanTinggiId(): ?int
    {
        return $this->perguruanTinggi?->getKey();
    }

    public function programStudiId(): ?int
    {
        return $this->programStudi?->getKey();
    }

    public function clear(): void
    {
        $this->yayasan = null;
        $this->perguruanTinggi = null;
        $this->programStudi = null;
    }

    public static function instance(): self
    {
        return App::make(self::class);
    }
}
