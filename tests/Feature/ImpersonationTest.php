<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\PerguruanTinggi;
use App\Models\User;
use App\Models\Yayasan;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_start_impersonation_and_can_return(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan Uji', 'kode' => 'YUJ']);
        $perguruanTinggi = PerguruanTinggi::query()->create([
            'yayasan_id' => $yayasan->id,
            'nama_pt' => 'Perguruan Tinggi Uji',
            'kode_pt' => 'PTUJ',
        ]);
        $superAdmin = User::factory()->create(['email' => 'superadmin@test.local']);
        $target = User::factory()->create([
            'email' => 'target@test.local',
            'yayasan_id' => $yayasan->id,
            'perguruan_tinggi_id' => $perguruanTinggi->id,
        ]);
        Role::findOrCreate('super_admin', 'web');
        Role::findOrCreate('pt_admin', 'web');
        $target->assignRole('pt_admin');
        $superAdmin->assignRole('super_admin');

        $this->actingAs($superAdmin)
            ->post(route('impersonation.start', $target))
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        self::assertAuthenticatedAs($target);
        self::assertSame($superAdmin->id, session('impersonation.original_user_id'));
        self::assertNull(session('password_hash_web'));
        $response = $this->get('/admin');
        self::assertNotSame(302, $response->status(), 'Sesi target tidak boleh diarahkan ke halaman login.');
        self::assertSame($target->id, session('impersonation.target_user_id'));
        self::assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'event' => 'impersonation.started',
        ]);

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('filament.admin.pages.dashboard'));

        self::assertAuthenticatedAs($superAdmin);
        self::assertNull(session('impersonation.original_user_id'));
        self::assertNull(session('password_hash_web'));
        self::assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'event' => 'impersonation.stopped',
        ]);
    }

    public function test_non_super_admin_cannot_start_impersonation(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor)
            ->post(route('impersonation.start', $target))
            ->assertForbidden();

        self::assertAuthenticatedAs($actor);
        self::assertDatabaseMissing('audit_logs', ['event' => 'impersonation.started']);
    }

    public function test_super_admin_cannot_impersonate_another_super_admin(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        $actor = User::factory()->create();
        $target = User::factory()->create();
        Role::findOrCreate('super_admin', 'web');
        $actor->assignRole('super_admin');
        $target->assignRole('super_admin');

        $this->actingAs($actor)
            ->post(route('impersonation.start', $target))
            ->assertForbidden();

        self::assertAuthenticatedAs($actor);
    }
}
