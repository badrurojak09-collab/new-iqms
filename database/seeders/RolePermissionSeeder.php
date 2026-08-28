<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view organization',
            'manage organization',
            'view security',
            'manage security',
            'view audit log',
            'view spmi',
            'manage spmi',
            'view ami',
            'manage ami',
            'review ami',
            'view accreditation',
            'manage accreditation',
            'review accreditation',
            'approve accreditation',
            'view evidence',
            'manage evidence',
            'review evidence',
            'view instrument configuration',
            'manage instrument configuration',
            'review instrument configuration',
            'approve instrument configuration',
            'resolve readiness gap',
            'manage rtl',
            'manage rtm',
            'verify rtl',
            'close rtl',
            'review rtl effectiveness',
            'submit rtl effectiveness',
            'approve rtl effectiveness',
            'verify spmi improvement',
        ];

        $permissionModels = collect($permissions)->mapWithKeys(
            fn (string $permission): array => [
                $permission => Permission::query()->firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]),
            ],
        );

        $rolePermissions = [
            'super_admin' => $permissions,
            'security_admin' => ['view security', 'manage security', 'view audit log'],
            'instrument_reviewer' => ['view accreditation', 'review accreditation', 'view instrument configuration', 'review instrument configuration'],
            'instrument_approver' => ['view accreditation', 'review accreditation', 'view instrument configuration', 'review instrument configuration', 'approve instrument configuration'],
            'quality_manager' => ['view spmi', 'manage spmi', 'view ami', 'review ami', 'view accreditation', 'review accreditation', 'view instrument configuration', 'manage instrument configuration', 'review instrument configuration', 'approve instrument configuration', 'view evidence', 'review evidence', 'resolve readiness gap', 'manage rtl', 'manage rtm', 'verify rtl', 'close rtl', 'review rtl effectiveness', 'submit rtl effectiveness', 'approve rtl effectiveness', 'verify spmi improvement'],
            'yayasan_admin' => ['view organization', 'manage organization', 'view accreditation', 'approve accreditation'],
            'pt_admin' => ['view organization', 'manage organization', 'view spmi', 'manage spmi', 'view ami', 'manage ami', 'view accreditation', 'manage accreditation', 'manage evidence'],
            'lpm' => ['view spmi', 'manage spmi', 'view ami', 'review ami', 'view accreditation', 'review accreditation', 'view evidence', 'review evidence', 'review rtl effectiveness', 'submit rtl effectiveness', 'verify spmi improvement'],
            'auditor' => ['view ami', 'manage ami', 'view evidence'],
            'kaprodi' => ['view spmi', 'manage spmi', 'view ami', 'review ami', 'view accreditation', 'manage accreditation', 'manage evidence'],
            'reviewer' => ['review ami', 'review accreditation', 'review evidence', 'review instrument configuration'],
        ];

        foreach ($rolePermissions as $roleName => $rolePermissionNames) {
            $rolePermissionNames = array_values(array_unique($rolePermissionNames));

            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissionModels->only($rolePermissionNames)->values());
        }
    }
}
