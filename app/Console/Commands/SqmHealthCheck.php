<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SqmHealthCheck extends Command
{
    protected $signature = 'sqm:health';

    protected $description = 'Memeriksa kesiapan dasar aplikasi SQM untuk pra-produksi.';

    public function handle(): int
    {
        $checks = [
            'Konfigurasi locale' => config('app.locale') === 'id' && config('app.timezone') === 'Asia/Jakarta',
            'Koneksi database' => $this->databaseWorks(),
            'Tabel migration' => Schema::hasTable('migrations'),
            'Tabel queue' => Schema::hasTable('jobs'),
            'Tabel tenant' => Schema::hasTable('perguruan_tinggi') && Schema::hasTable('user_tenant_scopes'),
            'Cache aplikasi' => $this->cacheWorks(),
        ];
        $failed = 0;
        foreach ($checks as $name => $passed) {
            $passed ? $this->info("[OK] {$name}") : $this->error("[GAGAL] {$name}");
            $failed += $passed ? 0 : 1;
        }
        $this->line('Lingkungan: ' . app()->environment());
        $this->line('Queue: ' . config('queue.default'));
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function databaseWorks(): bool
    {
        try { DB::select('select 1'); return true; } catch (Throwable) { return false; }
    }

    private function cacheWorks(): bool
    {
        try { Cache::put('sqm-health-check', 'ok', 10); return Cache::get('sqm-health-check') === 'ok'; } catch (Throwable) { return false; }
    }
}
