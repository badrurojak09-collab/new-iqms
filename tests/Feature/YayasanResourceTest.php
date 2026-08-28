<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YayasanResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_yayasan_generates_unique_code_when_code_is_empty(): void
    {
        $first = Yayasan::query()->create(['nama' => 'Yayasan Otomatis Satu']);
        $second = Yayasan::query()->create(['nama' => 'Yayasan Otomatis Dua']);

        self::assertSame('YYS-001', $first->kode);
        self::assertSame('YYS-002', $second->kode);
    }

    public function test_existing_code_is_preserved_and_not_replaced(): void
    {
        $yayasan = Yayasan::query()->create([
            'nama' => 'Yayasan Dengan Kode',
            'kode' => 'YAY-MANUAL',
        ]);

        self::assertSame('YAY-MANUAL', $yayasan->kode);
    }

    public function test_yayasan_counts_related_perguruan_tinggi(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Penghitung']);

        PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Satu',
            'kode_pt' => 'PT-001',
        ]);
        PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Dua',
            'kode_pt' => 'PT-002',
        ]);

        $result = Yayasan::query()
            ->withCount('perguruanTinggis')
            ->findOrFail($yayasan->id);

        self::assertSame(2, (int) $result->perguruan_tinggis_count);
    }
}

