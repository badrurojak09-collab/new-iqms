<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerguruanTinggiResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_perguruan_tinggi_generates_unique_code_when_code_is_empty(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan PT Otomatis']);

        $first = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Otomatis Satu',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);
        $second = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Otomatis Dua',
            'jenis' => 'institut',
            'status' => 'active',
        ]);

        self::assertSame('PT-001', $first->kode_pt);
        self::assertSame('PT-002', $second->kode_pt);
    }

    public function test_existing_pt_code_is_preserved(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan PT Manual']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Manual',
            'kode_pt' => 'PT-MANUAL',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);

        self::assertSame('PT-MANUAL', $pt->kode_pt);
    }
}

