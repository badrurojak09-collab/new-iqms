<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\PerguruanTinggi;
use App\Models\RtlAction;
use App\Models\User;
use App\Models\UserTenantScope;
use App\Models\Yayasan;
use Spatie\Permission\Models\Permission;
use App\Policies\RtlActionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

final class AmiRtlTenantHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_rtl_policy_rejects_record_outside_user_tenant(): void
    {
        [$user, $allowedPt] = $this->tenantContext('Diizinkan');
        [, $otherPt] = $this->tenantContext('Lain');
        Permission::query()->firstOrCreate(['name' => 'manage rtl', 'guard_name' => 'web']);
        $user->givePermissionTo('manage rtl');
        $action = RtlAction::withoutGlobalScopes()->create([
            'perguruan_tinggi_id' => $otherPt->id,
            'code' => 'RTL-OTHER-001',
            'title' => 'RTL tenant lain',
            'action_plan' => 'Tidak boleh terlihat',
            'status' => 'open',
        ]);

        self::assertNotSame($allowedPt->id, $action->perguruan_tinggi_id);
        self::assertFalse((new RtlActionPolicy())->view($user, $action));
    }

    public function test_rtl_query_returns_only_accessible_tenant(): void
    {
        [$user, $allowedPt] = $this->tenantContext('Diizinkan');
        [, $otherPt] = $this->tenantContext('Lain');
        RtlAction::withoutGlobalScopes()->insert([
            ['perguruan_tinggi_id' => $allowedPt->id, 'code' => 'RTL-ALLOW-001', 'title' => 'Diizinkan', 'action_plan' => 'Rencana', 'status' => 'open', 'created_at' => now(), 'updated_at' => now()],
            ['perguruan_tinggi_id' => $otherPt->id, 'code' => 'RTL-OTHER-002', 'title' => 'Lain', 'action_plan' => 'Rencana', 'status' => 'open', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($user);
        $ids = \App\Filament\Resources\RtlActions\RtlActionResource::getEloquentQuery()->pluck('perguruan_tinggi_id')->all();
        self::assertSame([$allowedPt->id], array_values(array_unique($ids)));
    }

    /** @return array{0: User, 1: PerguruanTinggi} */
    private function tenantContext(string $suffix): array
    {
        $yayasan = Yayasan::query()->create(['nama' => 'Yayasan '.$suffix, 'kode' => 'YA-'.uniqid()]);
        $pt = PerguruanTinggi::query()->create(['yayasan_id' => $yayasan->id, 'nama_pt' => 'PT '.$suffix, 'kode_pt' => 'PT-'.uniqid()]);
        $user = User::factory()->create(['yayasan_id' => $yayasan->id, 'perguruan_tinggi_id' => $pt->id]);
        UserTenantScope::query()->create([
            'user_id' => $user->id,
            'scope_type' => 'perguruan_tinggi',
            'scope_id' => $pt->id,
            'is_default' => true,
        ]);
        $user->flushTenantScopeCache();
        return [$user, $pt];
    }
}
