<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\InstrumentRegistry\ImportInstrumentVersion;
use App\Domain\InstrumentRegistry\PublishInstrumentVersion;
use App\Models\AccreditationBody;
use App\Models\InstrumentFamily;
use App\Models\InstrumentVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LogicException;
use Tests\TestCase;

final class InstrumentVersionTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_version_is_hashed_and_cannot_be_changed(): void
    {
        $version = $this->createVersion();
        $published = app(PublishInstrumentVersion::class)->handle($version, User::factory()->create()->id);

        self::assertSame('published', $published->status);
        self::assertNotNull($published->content_hash);
        self::assertTrue($published->isImmutable());

        $this->expectException(LogicException::class);
        $published->update(['version_label' => '2.0-revised']);
    }

    public function test_import_rejects_duplicate_node_codes(): void
    {
        $family = InstrumentFamily::query()->create([
            'code' => 'IAPS',
            'name' => 'IAPS',
            'scope_type' => 'study_program',
        ]);

        $this->expectException(ValidationException::class);
        app(ImportInstrumentVersion::class)->handle([
            'instrument_family_id' => $family->id,
            'version_label' => '2.1',
            'nodes' => [
                ['code' => 'A.1', 'node_type' => 'criterion', 'title' => 'One'],
                ['code' => 'A.1', 'node_type' => 'element', 'title' => 'Duplicate'],
            ],
        ]);
    }

    private function createVersion(): InstrumentVersion
    {
        $body = AccreditationBody::query()->create([
            'code' => 'BAN-PT',
            'name' => 'BAN-PT',
        ]);
        $family = InstrumentFamily::query()->create([
            'accreditation_body_id' => $body->id,
            'code' => 'IAPT',
            'name' => 'IAPT',
            'scope_type' => 'institution',
        ]);
        $version = $family->versions()->create([
            'version_label' => '4.1',
            'status' => 'review',
        ]);
        $version->nodes()->createMany([
            ['node_type' => 'criterion', 'code' => 'A', 'title' => 'Governance', 'sort_order' => 1],
        ]);

        return $version->fresh('nodes');
    }
}
