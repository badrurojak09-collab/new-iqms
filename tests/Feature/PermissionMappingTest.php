<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PermissionMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permission_seeder_creates_unique_and_expected_mappings(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $instrumentReviewer = Role::findByName('instrument_reviewer', 'web');
        $instrumentApprover = Role::findByName('instrument_approver', 'web');
        $securityAdmin = Role::findByName('security_admin', 'web');

        self::assertSame(
            $instrumentReviewer->permissions->pluck('name')->unique()->count(),
            $instrumentReviewer->permissions->count(),
        );
        self::assertSame(
            $instrumentApprover->permissions->pluck('name')->unique()->count(),
            $instrumentApprover->permissions->count(),
        );
        self::assertTrue($instrumentReviewer->hasPermissionTo('view instrument configuration'));
        self::assertTrue($instrumentReviewer->hasPermissionTo('review instrument configuration'));
        self::assertFalse($instrumentReviewer->hasPermissionTo('approve instrument configuration'));
        self::assertTrue($instrumentApprover->hasPermissionTo('approve instrument configuration'));
        self::assertFalse($instrumentApprover->hasPermissionTo('manage instrument configuration'));
        self::assertTrue($securityAdmin->hasPermissionTo('view security'));
        self::assertTrue($securityAdmin->hasPermissionTo('manage security'));
        self::assertTrue($securityAdmin->hasPermissionTo('view audit log'));
        self::assertSame(1, Permission::query()->where('name', 'view evidence')->count());
        self::assertSame(1, Permission::query()->where('name', 'manage instrument configuration')->count());
    }
}
