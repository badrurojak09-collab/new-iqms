<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Evidence;
use App\Models\EvidenceCollection;
use App\Models\PerguruanTinggi;
use App\Models\ProgramStudi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EvidenceResourceScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_query_is_scoped_by_tenant(): void
    {
        $yayasan1 = Yayasan::query()->create(['nama' => 'Yayasan A', 'kode' => 'YA']);
        $yayasan2 = Yayasan::query()->create(['nama' => 'Yayasan B', 'kode' => 'YB']);

        $pt1 = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan1->id, 'nama_pt' => 'PT 1', 'kode_pt' => 'PT1']);
        $pt2 = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan2->id, 'nama_pt' => 'PT 2', 'kode_pt' => 'PT2']);

        $prodi1 = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt1->id, 'nama_prodi' => 'Prodi 1', 'kode_prodi' => 'PR1']);
        $prodi2 = ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt1->id, 'nama_prodi' => 'Prodi 2', 'kode_prodi' => 'PR2']);

        $user = User::factory()->create(['yayasan_id' => null, 'perguruan_tinggi_id' => $pt1->id]);
        $user->programStudis()->attach($prodi1->id, ['peran' => 'kaprodi']);

        $evPtLevel = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt1->id,
            'program_studi_id' => null,
            'created_by' => $user->id,
            'code' => 'EVD-PT',
            'title' => 'Evidence Level PT',
        ]);
        $evProdi1 = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt1->id,
            'program_studi_id' => $prodi1->id,
            'created_by' => $user->id,
            'code' => 'EVD-PR1',
            'title' => 'Evidence Prodi 1',
        ]);
        $evProdi2 = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt1->id,
            'program_studi_id' => $prodi2->id,
            'created_by' => $user->id,
            'code' => 'EVD-PR2',
            'title' => 'Evidence Prodi 2',
        ]);
        $evOtherPt = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt2->id,
            'created_by' => $user->id,
            'code' => 'EVD-PT2',
            'title' => 'Evidence PT 2',
        ]);

        $this->actingAs($user);

        $results = \App\Filament\Resources\Evidences\EvidenceResource::getEloquentQuery()->pluck('id')->all();

        self::assertContains($evPtLevel->id, $results);
        self::assertContains($evProdi1->id, $results);
        self::assertNotContains($evProdi2->id, $results);
        self::assertNotContains($evOtherPt->id, $results);
    }

    public function test_evidence_collection_query_is_scoped_by_tenant(): void
    {
        $yayasan1 = Yayasan::query()->create(['nama' => 'Yayasan B1', 'kode' => 'YB1']);
        $yayasan2 = Yayasan::query()->create(['nama' => 'Yayasan B2', 'kode' => 'YB2']);

        $pt1 = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan1->id, 'nama_pt' => 'PT B1', 'kode_pt' => 'PTB1']);
        $pt2 = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan2->id, 'nama_pt' => 'PT B2', 'kode_pt' => 'PTB2']);

        $user = User::factory()->create(['yayasan_id' => null, 'perguruan_tinggi_id' => $pt1->id]);

        $col1 = EvidenceCollection::query()->create([
            'perguruan_tinggi_id' => $pt1->id,
            'created_by' => $user->id,
            'code' => 'COL-1',
            'name' => 'Koleksi 1',
            'provider' => 'google_drive',
        ]);
        $col2 = EvidenceCollection::query()->create([
            'perguruan_tinggi_id' => $pt2->id,
            'created_by' => $user->id,
            'code' => 'COL-2',
            'name' => 'Koleksi 2',
            'provider' => 'google_drive',
        ]);

        $this->actingAs($user);

        $results = \App\Filament\Resources\EvidenceCollections\EvidenceCollectionResource::getEloquentQuery()->pluck('id')->all();

        self::assertContains($col1->id, $results);
        self::assertNotContains($col2->id, $results);
    }
}
