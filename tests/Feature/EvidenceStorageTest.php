<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Evidence\StoreEvidenceDocument;
use App\Models\Evidence;
use App\Models\PerguruanTinggi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class EvidenceStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_stores_private_file_and_sha256_integrity(): void
    {
        Storage::fake('local');
        [$user, $evidence] = $this->createEvidenceContext();
        $this->actingAs($user);
        $file = UploadedFile::fake()->create('pedoman.pdf', 12, 'application/pdf');

        $version = app(StoreEvidenceDocument::class)->handle($user, $evidence, $file, 'initial upload');
        $document = $version->document;

        self::assertSame(1, $version->version_no);
        self::assertSame('private', $document->visibility);
        self::assertSame(hash_file('sha256', $file->getRealPath()), $document->sha256);
        Storage::disk('local')->assertExists($document->storage_path);
    }

    public function test_download_rejects_tampered_file(): void
    {
        Storage::fake('local');
        [$user, $evidence] = $this->createEvidenceContext();
        $this->actingAs($user);
        $version = app(StoreEvidenceDocument::class)->handle(
            $user,
            $evidence,
            UploadedFile::fake()->create('evidence.pdf', 10, 'application/pdf'),
        );
        $document = $version->document;
        Storage::disk('local')->put($document->storage_path, 'tampered-content');

        $url = URL::temporarySignedRoute(
            'evidence-versions.download',
            now()->addMinute(),
            ['evidenceVersion' => $version->id],
        );

        $this->actingAs($user)->get($url)->assertStatus(422);
    }

    /** @return array{0: User, 1: Evidence} */
    private function createEvidenceContext(): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Evidence', 'kode' => uniqid('YE')]);
        $pt = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'PT Evidence',
            'kode_pt' => uniqid('PTE'),
        ]);
        \App\Models\ProgramStudi::query()->create(['perguruan_tinggi_id' => $pt->id, 'nama_prodi' => 'Prodi Evidence', 'kode_prodi' => uniqid('PRE')]);
        $user = User::factory()->create([
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $pt->id,
        ]);
        $evidence = Evidence::query()->create([
            'perguruan_tinggi_id' => $pt->id,
            'created_by' => $user->id,
            'code' => uniqid('EVD'),
            'title' => 'Pedoman Mutu',
        ]);

        return [$user, $evidence];
    }
}
