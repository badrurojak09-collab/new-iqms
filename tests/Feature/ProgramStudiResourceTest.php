<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramStudiResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_studi_generates_unique_code_when_code_is_empty(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Prodi Otomatis']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Prodi Otomatis',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);

        $first = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Program Studi Otomatis Satu',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);
        $second = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Program Studi Otomatis Dua',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        self::assertSame('PRD-001', $first->kode_prodi);
        self::assertSame('PRD-002', $second->kode_prodi);
    }

    public function test_existing_program_study_code_is_preserved(): void
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Prodi Manual']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Prodi Manual',
            'jenis' => 'universitas',
            'status' => 'active',
        ]);

        $prodi = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Program Studi Manual',
            'kode_prodi' => 'PRODI-MANUAL',
            'jenjang' => 'S1',
            'status' => 'active',
        ]);

        self::assertSame('PRODI-MANUAL', $prodi->kode_prodi);
    }
}

