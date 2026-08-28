<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\PerguruanTinggis\PerguruanTinggiResource;
use App\Filament\Resources\ProgramStudis\ProgramStudiResource;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantAwareGlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_excludes_unrelated_perguruan_tinggi(): void
    {
        [$user, $pt, $otherPt] = $this->makeTenantFixture();
        $this->actingAs($user);

        $ids = PerguruanTinggiResource::getGlobalSearchEloquentQuery()
            ->whereIn('id', [$pt->id, $otherPt->id])
            ->pluck('id');

        self::assertTrue($ids->contains($pt->id));
        self::assertFalse($ids->contains($otherPt->id));
    }

    public function test_global_search_excludes_unassigned_program_study(): void
    {
        [$user, $pt] = $this->makeTenantFixture();
        $visible = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'nama_prodi' => 'Prodi Terlihat',
            'kode_prodi' => 'VISIBLE',
        ]);
        $otherPt = PerguruanTinggi::query()->create([
            'yayasan_id' => $pt->yayasan_id,
            'nama_pt' => 'PT Kedua',
            'kode_pt' => 'PT2',
        ]);
        $hidden = ProgramStudi::query()->create([
            'perguruan_tinggi_id' => $otherPt->id,
            'nama_prodi' => 'Prodi Tersembunyi',
            'kode_prodi' => 'HIDDEN',
        ]);
        $user->programStudis()->attach($visible->id, ['peran' => 'kaprodi']);
        $this->actingAs($user);

        $ids = ProgramStudiResource::getGlobalSearchEloquentQuery()
            ->whereIn('id', [$visible->id, $hidden->id])
            ->pluck('id');

        self::assertTrue($ids->contains($visible->id));
        self::assertFalse($ids->contains($hidden->id));
    }

    /** @return array{0: User, 1: PerguruanTinggi, 2: PerguruanTinggi} */
    private function makeTenantFixture(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Search A', 'kode' => 'YSA']);
        $otherYayasan = Yayasan::query()->create(['nama' => 'Yayasan Search B', 'kode' => 'YSB']);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Search A',
            'kode_pt' => 'PTSA',
        ]);
        $otherPt = PerguruanTinggi::query()->create([
            'yayasan_id' => $otherYayasan->id,
            'nama_pt' => 'PT Search B',
            'kode_pt' => 'PTSB',
        ]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);

        return [$user, $pt, $otherPt];
    }
}
